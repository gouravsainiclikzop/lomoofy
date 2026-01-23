<?php

namespace App\Http\Controllers;

use App\Services\RazorpayService;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    protected $razorpayService;

    public function __construct(RazorpayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

    /**
     * Handle Razorpay webhook
     */
    public function handle(Request $request)
    {
        try {
            // Get the raw payload
            $payload = $request->getContent();
            $signature = $request->header('X-Razorpay-Signature');

            // Verify webhook signature
            if (!$this->razorpayService->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Razorpay webhook signature verification failed', [
                    'ip' => $request->ip(),
                    'signature' => $signature
                ]);
                
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid signature'
                ], 401);
            }

            $data = json_decode($payload, true);
            $event = $data['event'] ?? null;

            Log::info('Razorpay webhook received', [
                'event' => $event,
                'payload' => $data
            ]);

            // Handle different webhook events
            switch ($event) {
                case 'payment.authorized':
                    $this->handlePaymentAuthorized($data);
                    break;

                case 'payment.captured':
                    $this->handlePaymentCaptured($data);
                    break;

                case 'payment.failed':
                    $this->handlePaymentFailed($data);
                    break;

                case 'order.paid':
                    $this->handleOrderPaid($data);
                    break;

                case 'refund.created':
                    $this->handleRefundCreated($data);
                    break;

                case 'refund.processed':
                    $this->handleRefundProcessed($data);
                    break;

                default:
                    Log::info('Razorpay webhook event not handled', ['event' => $event]);
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Razorpay webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment.authorized event
     */
    protected function handlePaymentAuthorized($data)
    {
        $payment = $data['payload']['payment']['entity'] ?? null;
        
        if (!$payment) {
            return;
        }

        $orderId = $payment['notes']['order_id'] ?? null;
        
        if (!$orderId) {
            Log::warning('Order ID not found in payment notes', ['payment' => $payment]);
            return;
        }

        $order = Order::find($orderId);
        
        if (!$order) {
            Log::warning('Order not found for payment.authorized', ['order_id' => $orderId]);
            return;
        }

        $order->update([
            'razorpay_payment_id' => $payment['id'],
            'payment_status' => 'authorized',
        ]);

        Log::info('Payment authorized for order', [
            'order_id' => $orderId,
            'payment_id' => $payment['id']
        ]);
    }

    /**
     * Handle payment.captured event
     */
    protected function handlePaymentCaptured($data)
    {
        $payment = $data['payload']['payment']['entity'] ?? null;
        
        if (!$payment) {
            return;
        }

        $orderId = $payment['notes']['order_id'] ?? null;
        
        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);
        
        if (!$order) {
            Log::warning('Order not found for payment.captured', ['order_id' => $orderId]);
            return;
        }

        $order->update([
            'razorpay_payment_id' => $payment['id'],
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);

        Log::info('Payment captured for order', [
            'order_id' => $orderId,
            'payment_id' => $payment['id'],
            'amount' => $payment['amount'] / 100
        ]);
    }

    /**
     * Handle payment.failed event
     */
    protected function handlePaymentFailed($data)
    {
        $payment = $data['payload']['payment']['entity'] ?? null;
        
        if (!$payment) {
            return;
        }

        $orderId = $payment['notes']['order_id'] ?? null;
        
        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);
        
        if (!$order) {
            Log::warning('Order not found for payment.failed', ['order_id' => $orderId]);
            return;
        }

        $order->update([
            'payment_status' => 'failed',
            'status' => 'cancelled',
        ]);

        Log::info('Payment failed for order', [
            'order_id' => $orderId,
            'payment_id' => $payment['id'],
            'error_code' => $payment['error_code'] ?? null,
            'error_description' => $payment['error_description'] ?? null
        ]);
    }

    /**
     * Handle order.paid event
     */
    protected function handleOrderPaid($data)
    {
        $orderData = $data['payload']['order']['entity'] ?? null;
        
        if (!$orderData) {
            return;
        }

        $orderId = $orderData['notes']['order_id'] ?? null;
        
        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);
        
        if (!$order) {
            Log::warning('Order not found for order.paid', ['order_id' => $orderId]);
            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);

        Log::info('Order paid', [
            'order_id' => $orderId,
            'razorpay_order_id' => $orderData['id']
        ]);
    }

    /**
     * Handle refund.created event
     */
    protected function handleRefundCreated($data)
    {
        $refund = $data['payload']['refund']['entity'] ?? null;
        
        if (!$refund) {
            return;
        }

        $paymentId = $refund['payment_id'] ?? null;
        
        if (!$paymentId) {
            return;
        }

        $order = Order::where('razorpay_payment_id', $paymentId)->first();
        
        if (!$order) {
            Log::warning('Order not found for refund.created', ['payment_id' => $paymentId]);
            return;
        }

        // Update order notes with refund info
        $notes = $order->notes ? $order->notes . "\n" : '';
        $notes .= "Refund initiated: {$refund['id']} - Amount: " . ($refund['amount'] / 100);
        
        $order->update([
            'notes' => $notes,
        ]);

        Log::info('Refund created for order', [
            'order_id' => $order->id,
            'refund_id' => $refund['id'],
            'amount' => $refund['amount'] / 100
        ]);
    }

    /**
     * Handle refund.processed event
     */
    protected function handleRefundProcessed($data)
    {
        $refund = $data['payload']['refund']['entity'] ?? null;
        
        if (!$refund) {
            return;
        }

        $paymentId = $refund['payment_id'] ?? null;
        
        if (!$paymentId) {
            return;
        }

        $order = Order::where('razorpay_payment_id', $paymentId)->first();
        
        if (!$order) {
            Log::warning('Order not found for refund.processed', ['payment_id' => $paymentId]);
            return;
        }

        // Update order status to refunded
        $order->update([
            'payment_status' => 'refunded',
            'status' => 'refunded',
        ]);

        Log::info('Refund processed for order', [
            'order_id' => $order->id,
            'refund_id' => $refund['id'],
            'amount' => $refund['amount'] / 100
        ]);
    }
}
