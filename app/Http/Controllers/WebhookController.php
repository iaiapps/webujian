<?php

namespace App\Http\Controllers;

use App\Models\CreditPurchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Mayar webhook callback
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleMayar(Request $request)
    {
        // Log semua webhook untuk debugging
        Log::info('Mayar Webhook Received', [
            'ip' => $request->ip(),
            'payload' => $request->all(),
        ]);

        // Validasi event type
        $event = $request->input('event');
        if ($event !== 'payment.received') {
            Log::info('Mayar Webhook Ignored', ['event' => $event]);

            return response()->json(['status' => 'ignored', 'reason' => 'event_not_supported']);
        }

        $data = $request->input('data');
        $extraData = $data['extraData'] ?? [];

        // Cari purchase berdasarkan internal_ref atau invoice_id
        $purchase = CreditPurchase::where('internal_ref', $extraData['internal_ref'] ?? '')
            ->orWhere('mayar_invoice_id', $data['paymentLinkId'] ?? '')
            ->first();

        if (! $purchase) {
            Log::error('Mayar Webhook: Purchase not found', [
                'internal_ref' => $extraData['internal_ref'] ?? null,
                'payment_link_id' => $data['paymentLinkId'] ?? null,
            ]);

            return response()->json(['status' => 'not_found'], 404);
        }

        // Idempotency check - sudah diproses?
        if ($purchase->status !== 'pending') {
            Log::info('Mayar Webhook: Purchase already processed', [
                'purchase_id' => $purchase->id,
                'current_status' => $purchase->status,
            ]);

            return response()->json(['status' => 'already_processed']);
        }

        // Verifikasi amount cocok (toleransi 1% untuk fee)
        $receivedAmount = $data['amount'] ?? 0;
        $expectedAmount = $purchase->amount;
        $tolerance = $expectedAmount * 0.01; // 1% tolerance

        if (abs($receivedAmount - $expectedAmount) > $tolerance) {
            Log::warning('Mayar Webhook: Amount mismatch', [
                'purchase_id' => $purchase->id,
                'expected' => $expectedAmount,
                'received' => $receivedAmount,
            ]);
            // Tetap proses tapi log warning
        }

        try {
            // Update purchase
            $purchase->markAsPaid($data['paymentMethod'] ?? null);
            $purchase->update(['mayar_response' => $request->all()]);

            // Tambah kredit ke user
            $user = User::find($purchase->user_id);
            if (! $user) {
                throw new \Exception('User not found: '.$purchase->user_id);
            }

            // Base credits
            $user->addCredits(
                amount: $purchase->credits_amount,
                type: 'purchase',
                description: "Pembelian {$purchase->creditPackage->name}",
                referenceId: $purchase->id,
                referenceType: 'credit_purchase'
            );

            // Bonus credits (jika ada)
            if ($purchase->bonus_credits > 0) {
                $user->addCredits(
                    amount: $purchase->bonus_credits,
                    type: 'bonus',
                    description: "Bonus dari {$purchase->creditPackage->name}",
                    referenceId: $purchase->id,
                    referenceType: 'credit_purchase'
                );
            }

            Log::info('Mayar Webhook: Purchase processed successfully', [
                'purchase_id' => $purchase->id,
                'user_id' => $user->id,
                'credits_added' => $purchase->total_credits,
            ]);

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Mayar Webhook: Processing failed', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle webhook history for debugging
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        // Endpoint ini bisa digunakan untuk melihat history webhook
        // Bisa ditambahkan fitur retry jika diperlukan
        return response()->json(['message' => 'Webhook history endpoint']);
    }
}
