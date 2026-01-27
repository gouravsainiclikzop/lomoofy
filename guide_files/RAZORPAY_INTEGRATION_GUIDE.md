# Razorpay Dynamic Integration Guide

## Overview

The Razorpay payment gateway is now **fully integrated with the Admin Integrations module**. All Razorpay credentials are managed through the database, and webhooks are automatically configured for real-time payment updates.

## Features Implemented

### ✅ Dynamic Configuration
- All Razorpay credentials pulled from `integrations` table
- No hardcoded API keys in code or `.env` file
- Configuration managed through Admin Panel UI
- Real-time enable/disable toggle

### ✅ RazorpayService Class
- Centralized service for all Razorpay operations
- Automatic configuration loading
- Built-in error handling
- Configuration validation

### ✅ Webhook Implementation
- Dedicated webhook endpoint
- Signature verification using stored webhook secret
- Handles multiple event types
- Automatic order status updates

### ✅ Frontend Integration
- Dynamic Razorpay key injection
- Payment method availability based on configuration status
- Currency and mode settings from database

---

## Setup Instructions

### Step 1: Configure Razorpay in Admin Panel

1. **Log in to Admin Panel**
2. Navigate to **Settings → Integrations**
3. Find the **Razorpay Payment** card
4. Fill in the following fields:

   - **Key ID**: Your Razorpay API Key ID (e.g., `rzp_test_xxxxx` or `rzp_live_xxxxx`)
   - **Key Secret**: Your Razorpay API Key Secret
   - **Webhook Secret**: Your Razorpay Webhook Secret
   - **Currency**: Select currency (Default: INR)
   - **Mode**: Choose Test or Live
   - **Status**: Toggle to **Enabled**

5. Click **"Save Configuration"**

### Step 2: Set Up Razorpay Webhook

1. **Copy Webhook URL** from the integration card:
   ```
   https://yourdomain.com/webhook/razorpay
   ```

2. **Log in to Razorpay Dashboard**
   - Go to **Settings → Webhooks**
   - Click **"+ Add New Webhook"**

3. **Configure Webhook**:
   - **Webhook URL**: Paste your webhook URL
   - **Secret**: Enter a strong secret (save this in Admin Panel too)
   - **Active Events**: Select the following events:
     - ✓ payment.authorized
     - ✓ payment.captured
     - ✓ payment.failed
     - ✓ order.paid
     - ✓ refund.created
     - ✓ refund.processed
   - **Status**: Active

4. Click **"Create Webhook"**

---

## Architecture

### Files Created/Modified

```
app/
├── Services/
│   └── RazorpayService.php          # Service class for Razorpay operations
├── Http/Controllers/
│   ├── RazorpayWebhookController.php # Handles webhook events
│   └── FrontendController.php        # Updated to use RazorpayService
routes/
└── web.php                           # Added webhook route
resources/views/admin/integrations/
└── index.blade.php                   # Shows webhook URL
```

### RazorpayService Methods

```php
// Check if configured
$razorpayService->isConfigured(): bool

// Create order
$razorpayService->createOrder($amount, $receiptId, $notes = []): array

// Verify payment signature
$razorpayService->verifyPaymentSignature($orderId, $paymentId, $signature): bool

// Verify webhook signature
$razorpayService->verifyWebhookSignature($payload, $signature): bool

// Fetch payment details
$razorpayService->fetchPayment($paymentId): ?array

// Get configuration values
$razorpayService->getKeyId(): ?string
$razorpayService->getCurrency(): string
$razorpayService->isTestMode(): bool
```

---

## Webhook Events Handled

### 1. payment.authorized
- Triggered when payment is authorized but not captured
- Updates order: `payment_status = 'authorized'`

### 2. payment.captured
- Triggered when payment is successfully captured
- Updates order: 
  - `payment_status = 'paid'`
  - `status = 'processing'`

### 3. payment.failed
- Triggered when payment fails
- Updates order:
  - `payment_status = 'failed'`
  - `status = 'cancelled'`

### 4. order.paid
- Triggered when order is marked as paid
- Updates order:
  - `payment_status = 'paid'`
  - `status = 'processing'`

### 5. refund.created
- Triggered when refund is initiated
- Adds refund information to order notes

### 6. refund.processed
- Triggered when refund is successfully processed
- Updates order:
  - `payment_status = 'refunded'`
  - `status = 'refunded'`

---

## Flow Diagrams

### Payment Creation Flow

```
Customer Clicks "Pay Now"
        ↓
Frontend sends AJAX request to /razorpay/create-order
        ↓
RazorpayService checks if configured
        ↓
Create order in database
        ↓
RazorpayService.createOrder() → Razorpay API
        ↓
Razorpay returns order_id
        ↓
Frontend receives razorpay_order_id and key_id
        ↓
Razorpay checkout opens
        ↓
Customer completes payment
        ↓
Frontend calls /razorpay/payment-success with signature
        ↓
RazorpayService.verifyPaymentSignature()
        ↓
Order updated: payment_status = 'paid', status = 'processing'
        ↓
Redirect to order confirmation
```

### Webhook Flow

```
Razorpay sends webhook POST to /webhook/razorpay
        ↓
RazorpayWebhookController receives request
        ↓
Extract X-Razorpay-Signature header
        ↓
RazorpayService.verifyWebhookSignature()
        ↓
If valid, parse event type
        ↓
Execute appropriate handler method
        ↓
Update order in database
        ↓
Log event
        ↓
Return 200 OK to Razorpay
```

---

## Frontend Usage

### Checkout Page

The checkout page now receives dynamic Razorpay configuration:

```php
// In controller
return view('frontend.checkout', [
    'razorpay' => [
        'enabled' => true/false,
        'key_id' => 'rzp_xxxx',
        'currency' => 'INR'
    ]
]);
```

### JavaScript Integration

```javascript
// Check if Razorpay is available
@if($razorpay['enabled'])
    // Initialize Razorpay
    const options = {
        key: "{{ $razorpay['key_id'] }}",
        currency: "{{ $razorpay['currency'] }}",
        // ... other options
    };
@else
    // Hide Razorpay payment option
    $('#razorpay-option').hide();
@endif
```

---

## Security Features

### 1. Signature Verification
- All payment callbacks verified with HMAC SHA256 signature
- Webhook events verified before processing
- Invalid signatures logged and rejected

### 2. Configuration Security
- Sensitive fields (key_secret, webhook_secret) masked in UI
- Stored encrypted in database
- Only accessible to admin users
- No exposure to frontend JavaScript

### 3. Webhook Protection
- IP logging for failed verification attempts
- Signature validation prevents unauthorized requests
- Event logging for audit trail

---

## Testing

### Test Mode Setup

1. In Admin Panel, set **Mode** to **"Test"**
2. Use Razorpay test credentials:
   - Key ID: `rzp_test_xxxxx`
   - Key Secret: `test_secret_xxxxx`

3. Use test card numbers:
   ```
   Success: 4111 1111 1111 1111
   Failure: 4000 0000 0000 0002
   ```

### Testing Webhooks Locally

Use ngrok or similar tool:

```bash
ngrok http 8000
```

Update webhook URL in Razorpay Dashboard:
```
https://your-ngrok-url.ngrok.io/webhook/razorpay
```

---

## Troubleshooting

### Payment Not Working

**Check:**
1. ✓ Razorpay integration is **Enabled** in Admin Panel
2. ✓ Key ID and Key Secret are correct
3. ✓ Mode (Test/Live) matches your keys
4. ✓ Browser console for JavaScript errors

**Solution:**
- Review logs: `storage/logs/laravel.log`
- Check Razorpay Dashboard → Payments for failed attempts

### Webhook Not Receiving Events

**Check:**
1. ✓ Webhook URL is publicly accessible
2. ✓ Webhook secret matches in Razorpay and Admin Panel
3. ✓ Events are selected in Razorpay Dashboard

**Solution:**
- Test webhook URL manually: `curl -X POST https://yourdomain.com/webhook/razorpay`
- Check Razorpay Dashboard → Webhooks → Logs

### Signature Verification Failed

**Check:**
1. ✓ Webhook secret is correct
2. ✓ Not modifying webhook payload

**Solution:**
- Re-generate webhook secret in Razorpay
- Update in Admin Panel
- Delete old webhook and create new one

---

## Migration from Hardcoded Credentials

If you were using environment variables:

### Old Way (❌ Don't use)
```php
$keyId = env('RAZORPAY_KEY_ID');
$keySecret = env('RAZORPAY_KEY_SECRET');
```

### New Way (✅ Use this)
```php
$razorpayService = app(\App\Services\RazorpayService::class);

if ($razorpayService->isConfigured()) {
    $order = $razorpayService->createOrder($amount, $receiptId);
}
```

### Steps:
1. Copy credentials from `.env` to Admin Panel
2. Remove from `.env`:
   ```
   # RAZORPAY_KEY_ID=rzp_test_xxxxx  # REMOVE
   # RAZORPAY_KEY_SECRET=xxxxx       # REMOVE
   ```
3. Update any custom code to use `RazorpayService`

---

## Advanced Usage

### Custom Payment Method

```php
use App\Services\RazorpayService;

class CustomPaymentController extends Controller
{
    protected $razorpay;
    
    public function __construct(RazorpayService $razorpay)
    {
        $this->razorpay = $razorpay;
    }
    
    public function processPayment($amount)
    {
        if (!$this->razorpay->isConfigured()) {
            return response()->json([
                'error' => 'Payment gateway not configured'
            ], 503);
        }
        
        $order = $this->razorpay->createOrder($amount, 'custom_' . time());
        
        return response()->json([
            'razorpay_order_id' => $order['id'],
            'key_id' => $this->razorpay->getKeyId(),
        ]);
    }
}
```

### Fetch Payment Details

```php
$razorpayService = app(\App\Services\RazorpayService::class);
$payment = $razorpayService->fetchPayment('pay_xxxxx');

if ($payment) {
    $status = $payment['status'];      // captured, failed, etc.
    $method = $payment['method'];      // card, upi, netbanking, etc.
    $amount = $payment['amount'] / 100; // Convert paise to rupees
}
```

---

## Monitoring & Logs

### Application Logs

All Razorpay operations are logged:

```bash
tail -f storage/logs/laravel.log | grep Razorpay
```

### Log Entries

- **Order Creation**: `Razorpay order creation...`
- **Payment Success**: `Razorpay payment successful...`
- **Webhook Events**: `Razorpay webhook received...`
- **Errors**: `Razorpay order creation failed...`

### Razorpay Dashboard

Monitor payments:
1. **Payments**: View all transactions
2. **Orders**: View created orders
3. **Webhooks**: Check delivery status
4. **Logs**: API request logs

---

## FAQs

**Q: Can I use both Test and Live mode?**
A: Yes, but only one mode can be active at a time. Switch in Admin Panel.

**Q: What happens if I disable Razorpay?**
A: Payment option will be hidden from checkout page.

**Q: Are credentials stored securely?**
A: Yes, in database with masked display in UI. Use HTTPS for production.

**Q: Can I use multiple payment gateways?**
A: Yes, add more providers in the Integrations module similarly.

**Q: How do I test refunds?**
A: Issue refund from Razorpay Dashboard → Payments → Select payment → Refund.

---

## Best Practices

1. ✓ **Always use HTTPS** in production
2. ✓ **Enable webhooks** for real-time updates
3. ✓ **Test thoroughly** in test mode before going live
4. ✓ **Monitor logs** regularly for errors
5. ✓ **Keep webhook secret** secure
6. ✓ **Validate all payments** server-side
7. ✓ **Handle failures** gracefully with user feedback

---

## Support

- **Razorpay Documentation**: https://razorpay.com/docs/
- **API Reference**: https://razorpay.com/docs/api/
- **Support**: https://razorpay.com/support/

---

**Version:** 1.0.0  
**Date:** January 23, 2026  
**Integration Type:** Dynamic Database Configuration
