<?php

// app/Http/Controllers/Guru/SubscriptionController.php
namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru', 'check.approved']);
    }

    public function index()
    {
        $user = Auth::user();

        // Get active subscription
        $activeSubscription = $user->activeSubscription;

        // Get subscription history
        $subscriptions = $user->subscriptions()
            ->with('histories')
            ->latest()
            ->paginate(10);

        return view('guru.subscription.index', compact('activeSubscription', 'subscriptions'));
    }

    public function pricing()
    {
        $user = Auth::user();

        // Get settings from database
        $limits = \App\Models\Setting::getByGroup('limits');
        $pricing = \App\Models\Setting::getByGroup('pricing');

        // Plan details
        $plans = [
            'free' => [
                'name' => 'FREE',
                'price' => 0,
                'max_students' => $limits['free_max_students'] ?? 30,
                'max_packages' => $limits['free_max_packages'] ?? 3,
                'max_questions' => $limits['free_max_questions'] ?? 100,
                'max_classes' => $limits['free_max_classes'] ?? 1,
                'features' => [
                    ($limits['free_max_students'] ?? 30) . ' Siswa Aktif',
                    ($limits['free_max_packages'] ?? 3) . ' Paket Tes',
                    ($limits['free_max_questions'] ?? 100) . ' Soal',
                    ($limits['free_max_classes'] ?? 1) . ' Kelas',
                    'Hasil tersimpan 30 hari',
                    'Fitur dasar',
                ],
            ],
            'pro' => [
                'name' => 'PRO',
                'price' => $pricing['pro_price_monthly'] ?? 149000,
                'price_yearly' => $pricing['pro_price_yearly'] ?? 1490000,
                'max_students' => $limits['pro_max_students'] ?? 150,
                'max_packages' => $limits['pro_max_packages'] ?? 999999,
                'max_questions' => $limits['pro_max_questions'] ?? 500,
                'max_classes' => $limits['pro_max_classes'] ?? 5,
                'features' => [
                    ($limits['pro_max_students'] ?? 150) . ' Siswa Aktif',
                    'Unlimited Paket Tes',
                    ($limits['pro_max_questions'] ?? 500) . ' Soal',
                    ($limits['pro_max_classes'] ?? 5) . ' Kelas',
                    'Hasil tersimpan 1 tahun',
                    'Import Excel',
                    'Export Hasil',
                    'Analisis per siswa',
                    'Custom branding',
                    'Email support',
                ],
            ],
            'advanced' => [
                'name' => 'ADVANCED',
                'price' => $pricing['advanced_price_monthly'] ?? 399000,
                'price_yearly' => $pricing['advanced_price_yearly'] ?? 3990000,
                'max_students' => $limits['advanced_max_students'] ?? 999999,
                'max_packages' => $limits['advanced_max_packages'] ?? 999999,
                'max_questions' => $limits['advanced_max_questions'] ?? 999999,
                'max_classes' => $limits['advanced_max_classes'] ?? 999999,
                'features' => [
                    'Unlimited Siswa',
                    'Unlimited Paket Tes',
                    'Unlimited Soal',
                    'Unlimited Kelas',
                    'Hasil tersimpan selamanya',
                    'Multi guru (akun tambahan)',
                    'API access',
                    'White label (opsional)',
                    'Analisis advanced',
                    'Priority support',
                    'Backup & restore',
                ],
            ],
        ];

        // Get payment settings
        $payment = \App\Models\Setting::getByGroup('payment');

        return view('guru.subscription.pricing', compact('plans', 'payment'));
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'plan' => ['required', 'in:pro,advanced'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'payment_proof' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $user = Auth::user();

        // Check if already has pending subscription
        $pendingSubscription = $user->subscriptions()
            ->where('status', 'pending')
            ->first();

        if ($pendingSubscription) {
            return redirect()->back()->with('error', 'Anda masih memiliki permintaan upgrade yang menunggu approval. Silakan tunggu konfirmasi admin.');
        }

        // Get pricing from database
        $pricing = \App\Models\Setting::getByGroup('pricing');
        
        // Calculate amount
        $amounts = [
            'pro' => [
                'monthly' => $pricing['pro_price_monthly'] ?? 149000,
                'yearly' => $pricing['pro_price_yearly'] ?? 1490000,
            ],
            'advanced' => [
                'monthly' => $pricing['advanced_price_monthly'] ?? 399000,
                'yearly' => $pricing['advanced_price_yearly'] ?? 3990000,
            ],
        ];

        $amount = $amounts[$request->plan][$request->billing_cycle];
        $duration = $request->billing_cycle === 'monthly' ? 1 : 12; // months

        // Upload payment proof
        $proofPath = $this->uploadPaymentProof($request->file('payment_proof'));

        // Generate invoice number
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(uniqid());

        // Create subscription request
        $subscription = $user->subscriptions()->create([
            'invoice_number' => $invoiceNumber,
            'plan' => $request->plan,
            'billing_cycle' => $request->billing_cycle,
            'amount' => $amount,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
            'expired_at' => now()->addMonths($duration),
            'proof_of_payment' => $proofPath,
        ]);

        // Create history
        $subscription->histories()->create([
            'user_id' => $user->id,
            'action' => 'created',
            'new_plan' => $request->plan,
            'amount' => $amount,
            'notes' => 'Permintaan upgrade menunggu approval admin',
        ]);

        // TODO: Send notification to admin
        // $this->notifyAdmin($subscription);

        return redirect()->route('guru.subscription.index')
            ->with('success', 'Permintaan upgrade berhasil dikirim! Admin akan memverifikasi pembayaran Anda dalam 1x24 jam.');
    }

    public function cancel(Subscription $subscription)
    {
        $user = Auth::user();

        // Check ownership
        if ($subscription->user_id !== $user->id) {
            abort(403);
        }

        // Only pending can be cancelled
        if ($subscription->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya permintaan pending yang bisa dibatalkan.');
        }

        // Delete payment proof
        if ($subscription->payment_gateway_response) {
            $data = json_decode($subscription->payment_gateway_response, true);
            if (isset($data['payment_proof'])) {
                Storage::disk('public')->delete($data['payment_proof']);
            }
        }

        $subscription->update(['status' => 'cancelled']);

        $subscription->histories()->create([
            'user_id' => $user->id,
            'action' => 'cancelled',
            'notes' => 'Dibatalkan oleh user',
        ]);

        return redirect()->back()->with('success', 'Permintaan upgrade berhasil dibatalkan.');
    }

    private function uploadPaymentProof($file)
    {
        $filename = 'payment_proof_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = 'payment_proofs/' . $filename;

        // Store file
        Storage::disk('public')->put($path, file_get_contents($file));

        return $path;
    }
}
