<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=AIzaSyA9FgHBCpt7bYIq8_6jwvBwfmyFozvxQlI';
    }

    /**
     * Extract transaction data from OCR text using Gemini AI
     */
    public function extractTransactionData(string $ocrText): array
    {
        try {
            Log::info('GeminiService: Starting extraction for OCR text', ['text_length' => strlen($ocrText)]);

            $prompt = $this->buildPrompt($ocrText);
            
            $response = Http::timeout(30)
                ->post($this->baseUrl, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('GeminiService: API response received', ['response' => $result]);

                $extractedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                return [
                    'success' => true,
                    'raw_response' => $extractedText,
                    'json_data' => json_decode($this->cleanJsonResponse($extractedText), true)
                ];
            } else {
                Log::error('GeminiService: API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => 'Gemini API request failed: ' . $response->status(),
                    'raw_response' => $response->body(),
                    'json_data' => null
                ];
            }

        } catch (\Exception $e) {
            Log::error('GeminiService: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'raw_response' => null,
                'json_data' => null,
                'parsed_data' => null
            ];
        }
    }

    /**
     * Build the prompt for Gemini AI
     */
    private function buildPrompt(string $ocrText): string
    {
        return "{$ocrText}

ini adalah hasil dari proses OCR untuk bill/struk belanja. Ambil data transaksi, dan ubahlah dalam bentuk json yang formatnya seperti ini:

{
    \"shop_name\": \"\",
    \"address\": \"\",
    \"items\": [
        {
            \"name\": \"\",
            \"quantity\": 0,
            \"price\": 0.0
        }
    ],
    \"total_amount\": 0.0,
    \"date\": \"YYYY-MM-DD\",
    \"payment_method\": \"\"
}

Jika tidak dapat menemukan informasi tertentu, kosongkan field tersebut. Berikan hanya JSON response tanpa explanation tambahan.";
    }

    /**
     * Clean JSON response from Gemini (remove markdown formatting)
     */
    private function cleanJsonResponse(string $response): string
    {
        // Remove markdown code blocks
        $cleaned = preg_replace('/```json\s*/', '', $response);
        $cleaned = preg_replace('/```\s*$/', '', $cleaned);
        
        // Remove any leading/trailing whitespace
        $cleaned = trim($cleaned);
        
        // Remove any text before the first {
        $firstBrace = strpos($cleaned, '{');
        if ($firstBrace !== false) {
            $cleaned = substr($cleaned, $firstBrace);
        }
        
        // Remove any text after the last }
        $lastBrace = strrpos($cleaned, '}');
        if ($lastBrace !== false) {
            $cleaned = substr($cleaned, 0, $lastBrace + 1);
        }
        
        return $cleaned;
    }
}