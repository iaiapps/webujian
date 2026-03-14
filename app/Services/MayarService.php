<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MayarService
{
    private string $baseUrl;

    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.mayar.base_url', 'https://api.mayar.id/hl/v1');
        $this->apiKey = config('services.mayar.api_key');
    }

    /**
     * Create invoice for credit purchase
     *
     * @throws \Exception
     */
    public function createInvoice(array $data): array
    {
        $endpoint = $this->baseUrl.'/invoice/create';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($endpoint, $data);

            if (! $response->successful()) {
                Log::error('Mayar Create Invoice Failed', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                    'data' => $data,
                ]);

                throw new \Exception('Failed to create invoice: '.$response->body());
            }

            $result = $response->json();

            Log::info('Mayar Invoice Created', [
                'invoice_id' => $result['data']['id'] ?? null,
                'user_id' => $data['extraData']['user_id'] ?? null,
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Mayar Create Invoice Exception', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Get invoice status by ID
     *
     * @throws \Exception
     */
    public function getInvoiceStatus(string $invoiceId): array
    {
        $endpoint = $this->baseUrl.'/invoice/'.$invoiceId;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
            ])->get($endpoint);

            if (! $response->successful()) {
                Log::error('Mayar Get Invoice Status Failed', [
                    'invoice_id' => $invoiceId,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                throw new \Exception('Failed to get invoice status: '.$response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Mayar Get Invoice Status Exception', [
                'invoice_id' => $invoiceId,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Close an invoice (for expired purchases)
     *
     * @throws \Exception
     */
    public function closeInvoice(string $invoiceId): array
    {
        $endpoint = $this->baseUrl.'/invoice/'.$invoiceId.'/close';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
            ])->post($endpoint);

            if (! $response->successful()) {
                Log::error('Mayar Close Invoice Failed', [
                    'invoice_id' => $invoiceId,
                    'status' => $response->status(),
                ]);

                throw new \Exception('Failed to close invoice: '.$response->body());
            }

            Log::info('Mayar Invoice Closed', ['invoice_id' => $invoiceId]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Mayar Close Invoice Exception', [
                'invoice_id' => $invoiceId,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Validate webhook payload signature (if Mayar provides signature validation)
     * Note: Currently Mayar doesn't document signature validation
     */
    public function validateWebhookSignature(array $payload, string $signature): bool
    {
        // TODO: Implement if Mayar provides webhook signature
        // For now, validate by checking invoice_id exists in our database
        return true;
    }

    /**
     * Generate internal reference number
     */
    public function generateInternalRef(int $userId, int $packageId): string
    {
        return 'CRD-'.$userId.'-'.$packageId.'-'.time();
    }
}
