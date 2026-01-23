<?php

namespace App\Services;

use App\Models\Integration;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected $config;
    protected $isConfigured = false;

    public function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Load Razorpay configuration from database
     */
    protected function loadConfiguration()
    {
        $integration = Integration::byType('payment')
            ->where('provider', 'razorpay')
            ->active()
            ->first();

        if ($integration) {
            $this->config = $integration->configuration;
            $this->isConfigured = true;
        }
    }

    /**
     * Check if Razorpay is configured and active
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured && !empty($this->config['key_id']) && !empty($this->config['key_secret']);
    }

    /**
     * Get configuration value
     */
    public function getConfig($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Create Razorpay order
     */
    public function createOrder($amount, $receiptId, $notes = [])
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Razorpay is not configured. Please configure it in Settings → Integrations.');
        }

        $amountInPaise = round($amount * 100);
        
        $orderData = [
            'amount' => $amountInPaise,
            'currency' => $this->config['currency'] ?? 'INR',
            'receipt' => $receiptId,
            'notes' => $notes
        ];

        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['key_id'] . ':' . $this->config['key_secret']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            Log::error('Razorpay order creation failed', [
                'response' => $response,
                'http_code' => $httpCode,
                'error' => $error
            ]);
            
            throw new \Exception('Failed to create Razorpay order. Please try again.');
        }

        return json_decode($response, true);
    }

    /**
     * Verify payment signature
     */
    public function verifyPaymentSignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $generatedSignature = hash_hmac(
            'sha256',
            $razorpayOrderId . '|' . $razorpayPaymentId,
            $this->config['key_secret']
        );

        return hash_equals($generatedSignature, $razorpaySignature);
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $webhookSecret = $this->config['webhook_secret'] ?? null;
        
        if (!$webhookSecret) {
            Log::warning('Razorpay webhook secret not configured');
            return false;
        }

        $generatedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($generatedSignature, $signature);
    }

    /**
     * Fetch payment details
     */
    public function fetchPayment($paymentId)
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Razorpay is not configured');
        }

        $ch = curl_init("https://api.razorpay.com/v1/payments/{$paymentId}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['key_id'] . ':' . $this->config['key_secret']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Log::error('Razorpay payment fetch failed', [
                'payment_id' => $paymentId,
                'http_code' => $httpCode
            ]);
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Get Key ID for frontend
     */
    public function getKeyId(): ?string
    {
        return $this->config['key_id'] ?? null;
    }

    /**
     * Check if in test mode
     */
    public function isTestMode(): bool
    {
        return ($this->config['mode'] ?? 'test') === 'test';
    }

    /**
     * Get currency
     */
    public function getCurrency(): string
    {
        return $this->config['currency'] ?? 'INR';
    }
}
