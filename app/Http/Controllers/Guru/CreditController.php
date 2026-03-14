<?php

// app/Http/Controllers/Guru/CreditController.php
// ============================================================
// SISTEM KREDIT - Integrasi dengan Mayar Payment Gateway
// ============================================================

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use App\Models\CreditPurchase;
use App\Models\Setting;
use App\Services\MayarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreditController extends Controller
{
    private MayarService $mayarService;

    public function __construct(MayarService $mayarService)
    {
        $this->middleware(['auth', 'role:guru', 'check.approved']);
        $this->mayarService = $mayarService;
    }

    public function index()
    {
        $user = Auth::user();

        $creditInfo = [
            'current_credits' => $user->credits,
            'can_create_package' => $user->canCreatePackage(),
        ];

        // Ambil 5 transaksi terakhir
        $recentTransactions = $user->creditTransactions()->take(5)->get();

        $payment = Setting::getByGroup('payment');

        return view('guru.credits.index', compact('creditInfo', 'recentTransactions', 'payment'));
    }

    public function history()
    {
        $user = Auth::user();
        $transactions = $user->creditTransactions()->paginate(20);

        $stats = [
            'total_in' => $user->creditTransactions()->in()->sum('amount'),
            'total_out' => abs($user->creditTransactions()->out()->sum('amount')),
        ];

        return view('guru.credits.history', compact('transactions', 'stats'));
    }

    public function topup()
    {
        $user = Auth::user();

        // Ambil paket kredit aktif dari database
        $creditPackages = CreditPackage::active()->ordered()->get();

        $payment = Setting::getByGroup('payment');

        return view('guru.credits.topup', compact('creditPackages', 'payment'));
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'package_id' => ['required', 'exists:credit_packages,id'],
        ]);

        $user = Auth::user();
        $package = CreditPackage::findOrFail($request->package_id);

        // Generate internal reference
        $internalRef = $this->mayarService->generateInternalRef($user->id, $package->id);

        // Create invoice data
        $invoiceData = [
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->phone ?? '081000000000',
            'redirectUrl' => route('guru.credits.success'),
            'description' => "Pembelian {$package->name}",
            'expiredAt' => now()->addHours(24)->toIso8601String(),
            'items' => [[
                'quantity' => 1,
                'rate' => $package->price,
                'description' => $package->name,
            ]],
            'extraData' => [
                'internal_ref' => $internalRef,
                'user_id' => $user->id,
                'package_id' => $package->id,
                'credits' => $package->credit_amount,
                'bonus' => $package->bonus_credits,
            ],
        ];

        try {
            // Create invoice di Mayar
            $response = $this->mayarService->createInvoice($invoiceData);

            // Simpan ke database
            $purchase = CreditPurchase::create([
                'user_id' => $user->id,
                'credit_package_id' => $package->id,
                'mayar_invoice_id' => $response['data']['id'],
                'mayar_transaction_id' => $response['data']['transactionId'],
                'payment_link' => $response['data']['link'],
                'amount' => $package->price,
                'credits_amount' => $package->credit_amount,
                'bonus_credits' => $package->bonus_credits,
                'total_credits' => $package->getTotalCredits(),
                'expired_at' => Carbon::createFromTimestampMs($response['data']['expiredAt']),
                'internal_ref' => $internalRef,
                'status' => 'pending',
            ]);

            // Redirect ke halaman pembayaran Mayar
            return redirect($response['data']['link']);

        } catch (\Exception $e) {
            Log::error('Credit Purchase Failed', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Gagal membuat invoice pembayaran. Silakan coba lagi.');
        }
    }

    public function success()
    {
        $user = Auth::user();

        // Ambil purchase terakhir yang pending
        $purchase = CreditPurchase::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (! $purchase) {
            return redirect()->route('guru.credits.index');
        }

        return view('guru.credits.success', compact('purchase'));
    }

    /**
     * Check purchase status via AJAX polling
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'purchase_id' => ['required', 'exists:credit_purchases,id'],
        ]);

        $user = Auth::user();
        $purchase = CreditPurchase::where('id', $request->purchase_id)
            ->where('user_id', $user->id)
            ->first();

        if (! $purchase) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        // Jika masih pending, cek ke Mayar API
        if ($purchase->isPending()) {
            try {
                $mayarStatus = $this->mayarService->getInvoiceStatus($purchase->mayar_invoice_id);

                if (isset($mayarStatus['data']['status'])) {
                    $mayarInvoiceStatus = $mayarStatus['data']['status'];

                    // Update status jika berbeda
                    if ($mayarInvoiceStatus === 'paid' && $purchase->isPending()) {
                        $purchase->markAsPaid($mayarStatus['data']['paymentMethod'] ?? null);

                        // Tambah kredit
                        $this->processPurchaseCredits($purchase);
                    } elseif ($mayarInvoiceStatus === 'expired' && $purchase->isPending()) {
                        $purchase->markAsExpired();
                    }
                }
            } catch (\Exception $e) {
                Log::error('Check Status Failed', [
                    'purchase_id' => $purchase->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => $purchase->status,
            'status_label' => $purchase->getStatusLabel(),
            'is_paid' => $purchase->isPaid(),
            'is_expired' => $purchase->isExpired(),
            'total_credits' => $purchase->total_credits,
            'current_credits' => $user->fresh()->credits,
        ]);
    }

    /**
     * Process credits for a paid purchase
     */
    private function processPurchaseCredits(CreditPurchase $purchase): void
    {
        $user = $purchase->user;

        // Base credits
        $user->addCredits(
            amount: $purchase->credits_amount,
            type: 'purchase',
            description: "Pembelian {$purchase->creditPackage->name}",
            referenceId: $purchase->id,
            referenceType: 'credit_purchase'
        );

        // Bonus credits
        if ($purchase->bonus_credits > 0) {
            $user->addCredits(
                amount: $purchase->bonus_credits,
                type: 'bonus',
                description: "Bonus dari {$purchase->creditPackage->name}",
                referenceId: $purchase->id,
                referenceType: 'credit_purchase'
            );
        }
    }

    public function cancel(Request $request)
    {
        session()->forget('credit_purchase');

        return redirect()->route('guru.credits.index');
    }
}
