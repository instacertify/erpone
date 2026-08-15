<?php

namespace App\Services\Gst;

use App\Services\Settings\ErpSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GstApiClient
{
    public function __construct(private readonly ErpSettings $settings) {}

    public function isConfigured(): bool
    {
        return (bool) $this->settings->get('gst_enabled', false)
            && filled($this->settings->get('gst_api_base_url'))
            && filled($this->settings->get('gst_api_key'));
    }

    /**
     * @return array{ok: bool, message: string, data?: array<string, mixed>}
     */
    public function verifyGstin(string $gstin): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'GST API is not configured. Add credentials in Admin → Settings.',
            ];
        }

        $baseUrl = rtrim((string) $this->settings->get('gst_api_base_url'), '/');
        $apiKey = (string) $this->settings->get('gst_api_key');

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                    'Accept' => 'application/json',
                    'X-Api-Key' => $apiKey,
                ])
                ->get("{$baseUrl}/gstin/{$gstin}");

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'message' => 'GST API request failed (HTTP '.$response->status().').',
                    'data' => $response->json() ?? [],
                ];
            }

            return [
                'ok' => true,
                'message' => 'GSTIN verified successfully.',
                'data' => $response->json() ?? [],
            ];
        } catch (\Throwable $e) {
            Log::warning('GST API error', ['error' => $e->getMessage()]);

            return [
                'ok' => false,
                'message' => 'Unable to reach GST API: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Enable GST and provide API base URL + API key first.',
            ];
        }

        $baseUrl = rtrim((string) $this->settings->get('gst_api_base_url'), '/');
        $apiKey = (string) $this->settings->get('gst_api_key');

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                    'Accept' => 'application/json',
                    'X-Api-Key' => $apiKey,
                ])
                ->get("{$baseUrl}/health");

            return [
                'ok' => $response->successful(),
                'message' => $response->successful()
                    ? 'GST API connection successful.'
                    : 'GST API responded with HTTP '.$response->status().'.',
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Connection failed: '.$e->getMessage(),
            ];
        }
    }
}
