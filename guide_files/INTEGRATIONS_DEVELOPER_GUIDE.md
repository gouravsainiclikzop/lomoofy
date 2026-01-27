# Integrations Module - Developer Integration Guide

## 🎯 Purpose

This guide explains how to retrieve and use the stored integration configurations in your application logic.

## 📚 Table of Contents

1. [Retrieving Configurations](#retrieving-configurations)
2. [Integration Examples](#integration-examples)
3. [Best Practices](#best-practices)
4. [Error Handling](#error-handling)
5. [Testing](#testing)

---

## Retrieving Configurations

### Basic Retrieval

```php
use App\Models\Integration;

// Get specific integration by type
$emailConfig = Integration::byType('email')->first();

// Get active integrations only
$activeIntegrations = Integration::active()->get();

// Get specific provider
$razorpay = Integration::byType('payment')
    ->where('provider', 'razorpay')
    ->first();
```

### Accessing Configuration Data

```php
// Get raw configuration (with sensitive data)
$config = $emailConfig->configuration;

// Access specific fields
$smtpHost = $config['smtp_host'];
$smtpPort = $config['smtp_port'];
$password = $config['password'];

// Get masked configuration (for display purposes)
$maskedConfig = $emailConfig->getMaskedConfiguration();
// password will be '••••••••'
```

### Check if Integration is Active

```php
if ($emailConfig && $emailConfig->status) {
    // Integration is enabled, proceed with usage
} else {
    // Integration is disabled or not configured
}
```

---

## Integration Examples

### 1. Email (SMTP) Integration

#### Using with Laravel Mail

```php
use Illuminate\Support\Facades\Config;
use App\Models\Integration;

class EmailService
{
    public function configureMailer()
    {
        $emailIntegration = Integration::byType('email')->active()->first();
        
        if (!$emailIntegration) {
            throw new \Exception('Email integration not configured');
        }
        
        $config = $emailIntegration->configuration;
        
        // Set mail configuration dynamically
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $config['smtp_host'],
            'port' => $config['smtp_port'],
            'encryption' => $config['encryption'],
            'username' => $config['username'],
            'password' => $config['password'],
        ]);
        
        Config::set('mail.from', [
            'address' => $config['from_email'],
            'name' => $config['from_name'],
        ]);
    }
    
    public function sendOrderConfirmation($order)
    {
        $this->configureMailer();
        
        Mail::to($order->customer->email)
            ->send(new OrderConfirmation($order));
    }
}
```

---

### 2. Razorpay Payment Integration

```php
use App\Models\Integration;
use Razorpay\Api\Api;

class PaymentService
{
    protected $razorpay;
    protected $config;
    
    public function __construct()
    {
        $integration = Integration::byType('payment')
            ->where('provider', 'razorpay')
            ->active()
            ->first();
        
        if (!$integration) {
            throw new \Exception('Razorpay not configured');
        }
        
        $this->config = $integration->configuration;
        $this->razorpay = new Api(
            $this->config['key_id'],
            $this->config['key_secret']
        );
    }
    
    public function createOrder($amount, $orderId)
    {
        $orderData = [
            'receipt' => $orderId,
            'amount' => $amount * 100, // Convert to paise
            'currency' => $this->config['currency'],
            'payment_capture' => 1
        ];
        
        return $this->razorpay->order->create($orderData);
    }
    
    public function verifyPayment($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)
    {
        $attributes = [
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => $razorpayPaymentId,
            'razorpay_signature' => $razorpaySignature
        ];
        
        $this->razorpay->utility->verifyPaymentSignature($attributes);
    }
    
    public function isTestMode()
    {
        return $this->config['mode'] === 'test';
    }
}
```

---

### 3. OTP Service Integration

```php
use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class OtpService
{
    protected $config;
    
    public function __construct()
    {
        $integration = Integration::byType('otp')->active()->first();
        
        if (!$integration) {
            throw new \Exception('OTP service not configured');
        }
        
        $this->config = $integration->configuration;
    }
    
    public function generateOtp()
    {
        $length = $this->config['otp_length'] ?? 6;
        return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }
    
    public function sendOtp($phoneNumber, $otp)
    {
        // Example for MSG91
        if ($this->config['provider_name'] === 'MSG91') {
            $response = Http::post('https://api.msg91.com/api/v5/otp', [
                'authkey' => $this->config['api_key'],
                'mobile' => $phoneNumber,
                'sender' => $this->config['sender_id'],
                'DLT_TE_ID' => $this->config['template_id'],
                'otp' => $otp,
            ]);
            
            return $response->successful();
        }
        
        // Add other providers as needed
        return false;
    }
    
    public function getExpiryTime()
    {
        return $this->config['otp_expiry'] ?? 300; // seconds
    }
}
```

---

### 4. WhatsApp Messaging Integration

```php
use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected $config;
    
    public function __construct()
    {
        $integration = Integration::byType('whatsapp')->active()->first();
        
        if (!$integration) {
            throw new \Exception('WhatsApp service not configured');
        }
        
        $this->config = $integration->configuration;
    }
    
    public function sendMessage($toNumber, $message)
    {
        // Meta (Facebook) WhatsApp API
        $url = "https://graph.facebook.com/v17.0/{$this->config['phone_number_id']}/messages";
        
        $response = Http::withToken($this->config['access_token'])
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $toNumber,
                'text' => ['body' => $message]
            ]);
        
        return $response->successful();
    }
    
    public function sendTemplate($toNumber, $templateName, $parameters = [])
    {
        $url = "https://graph.facebook.com/v17.0/{$this->config['phone_number_id']}/messages";
        
        $response = Http::withToken($this->config['access_token'])
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $toNumber,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => ['code' => 'en'],
                    'components' => $parameters
                ]
            ]);
        
        return $response->successful();
    }
}
```

---

### 5. Shopify Integration

```php
use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class ShopifyService
{
    protected $config;
    protected $baseUrl;
    
    public function __construct()
    {
        $integration = Integration::byType('shopify')->active()->first();
        
        if (!$integration) {
            throw new \Exception('Shopify not configured');
        }
        
        $this->config = $integration->configuration;
        $this->baseUrl = rtrim($this->config['store_url'], '/') . 
                        '/admin/api/' . $this->config['api_version'];
    }
    
    public function getProducts($limit = 50)
    {
        if (!$this->config['sync_products']) {
            return [];
        }
        
        $response = Http::withToken($this->config['admin_api_access_token'])
            ->get($this->baseUrl . '/products.json', [
                'limit' => $limit
            ]);
        
        return $response->json('products', []);
    }
    
    public function getOrders($status = 'any')
    {
        if (!$this->config['sync_orders']) {
            return [];
        }
        
        $response = Http::withToken($this->config['admin_api_access_token'])
            ->get($this->baseUrl . '/orders.json', [
                'status' => $status
            ]);
        
        return $response->json('orders', []);
    }
    
    public function syncProduct($productData)
    {
        $response = Http::withToken($this->config['admin_api_access_token'])
            ->post($this->baseUrl . '/products.json', [
                'product' => $productData
            ]);
        
        return $response->json('product');
    }
}
```

---

### 6. Google Analytics Integration

```php
use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class AnalyticsService
{
    protected $config;
    
    public function __construct()
    {
        $integration = Integration::byType('analytics')->active()->first();
        
        if (!$integration) {
            throw new \Exception('Analytics not configured');
        }
        
        $this->config = $integration->configuration;
    }
    
    public function trackEvent($clientId, $eventName, $params = [])
    {
        if ($this->config['tracking_type'] !== 'ga4') {
            return false;
        }
        
        $url = "https://www.google-analytics.com/mp/collect";
        $queryParams = [
            'measurement_id' => $this->config['measurement_id'],
            'api_secret' => $this->config['api_secret']
        ];
        
        $data = [
            'client_id' => $clientId,
            'events' => [
                [
                    'name' => $eventName,
                    'params' => $params
                ]
            ]
        ];
        
        $response = Http::post($url, $data)->withQueryParameters($queryParams);
        return $response->successful();
    }
    
    public function trackPurchase($clientId, $transactionData)
    {
        if (!$this->config['enable_ecommerce']) {
            return false;
        }
        
        return $this->trackEvent($clientId, 'purchase', $transactionData);
    }
    
    public function getMeasurementId()
    {
        return $this->config['measurement_id'];
    }
}
```

---

## Best Practices

### 1. Service Classes Pattern

Create dedicated service classes for each integration:

```php
app/
├── Services/
│   ├── EmailService.php
│   ├── PaymentService.php
│   ├── OtpService.php
│   ├── WhatsAppService.php
│   ├── ShopifyService.php
│   └── AnalyticsService.php
```

### 2. Configuration Caching

Cache integration configurations to reduce database queries:

```php
use Illuminate\Support\Facades\Cache;

class IntegrationHelper
{
    public static function getConfig($type, $provider = null)
    {
        $cacheKey = "integration_{$type}" . ($provider ? "_{$provider}" : '');
        
        return Cache::remember($cacheKey, 3600, function () use ($type, $provider) {
            $query = Integration::byType($type)->active();
            
            if ($provider) {
                $query->where('provider', $provider);
            }
            
            $integration = $query->first();
            return $integration ? $integration->configuration : null;
        });
    }
    
    public static function clearCache($type, $provider = null)
    {
        $cacheKey = "integration_{$type}" . ($provider ? "_{$provider}" : '');
        Cache::forget($cacheKey);
    }
}

// Usage
$config = IntegrationHelper::getConfig('email');
```

### 3. Fallback Handling

Always provide fallbacks for disabled integrations:

```php
public function sendNotification($customer, $message)
{
    $whatsapp = Integration::byType('whatsapp')->active()->first();
    $email = Integration::byType('email')->active()->first();
    
    if ($whatsapp) {
        // Try WhatsApp first
        $sent = $this->sendWhatsApp($customer->phone, $message);
        if ($sent) return true;
    }
    
    if ($email) {
        // Fallback to email
        return $this->sendEmail($customer->email, $message);
    }
    
    // Log that no notification method is available
    Log::warning('No notification service configured');
    return false;
}
```

### 4. Environment-Based Overrides

Allow environment variables to override stored configs for development:

```php
public function getSmtpHost()
{
    return env('MAIL_HOST') ?? $this->config['smtp_host'];
}
```

---

## Error Handling

### Integration Not Found

```php
try {
    $emailConfig = Integration::byType('email')->firstOrFail();
} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    // Handle missing integration
    Log::error('Email integration not configured');
    return response()->json([
        'error' => 'Email service not configured. Please contact administrator.'
    ], 503);
}
```

### Integration Disabled

```php
$integration = Integration::byType('otp')->first();

if (!$integration || !$integration->status) {
    throw new \Exception('OTP service is currently unavailable');
}
```

### API Failures

```php
try {
    $response = $this->sendOtp($phone, $otp);
    
    if (!$response) {
        Log::error('OTP send failed', ['phone' => $phone]);
        throw new \Exception('Failed to send OTP. Please try again.');
    }
} catch (\Exception $e) {
    Log::error('OTP Service Error: ' . $e->getMessage());
    throw $e;
}
```

---

## Testing

### Unit Tests

```php
use Tests\TestCase;
use App\Models\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Create test integration
        Integration::create([
            'integration_type' => 'payment',
            'provider' => 'razorpay',
            'configuration' => [
                'key_id' => 'rzp_test_123',
                'key_secret' => 'test_secret',
                'webhook_secret' => 'test_webhook',
                'currency' => 'INR',
                'mode' => 'test'
            ],
            'status' => true
        ]);
    }
    
    public function test_payment_service_initialization()
    {
        $service = new PaymentService();
        $this->assertNotNull($service);
    }
    
    public function test_throws_exception_when_not_configured()
    {
        Integration::truncate();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Razorpay not configured');
        
        new PaymentService();
    }
}
```

### Feature Tests

```php
public function test_can_send_email_when_configured()
{
    Integration::create([
        'integration_type' => 'email',
        'configuration' => [
            'smtp_host' => 'smtp.mailtrap.io',
            'smtp_port' => 2525,
            'encryption' => 'tls',
            'username' => 'test',
            'password' => 'test',
            'from_email' => 'test@example.com',
            'from_name' => 'Test'
        ],
        'status' => true
    ]);
    
    Mail::fake();
    
    $service = new EmailService();
    $service->sendOrderConfirmation($order);
    
    Mail::assertSent(OrderConfirmation::class);
}
```

---

## Middleware for Integration Checks

Create middleware to check if required integrations are active:

```php
namespace App\Http\Middleware;

use Closure;
use App\Models\Integration;

class RequirePaymentIntegration
{
    public function handle($request, Closure $next)
    {
        $payment = Integration::byType('payment')->active()->first();
        
        if (!$payment) {
            return response()->json([
                'error' => 'Payment service is currently unavailable'
            ], 503);
        }
        
        return $next($request);
    }
}
```

Usage in routes:

```php
Route::post('/checkout', [CheckoutController::class, 'process'])
    ->middleware('require.payment');
```

---

## Events and Listeners

Dispatch events when integration status changes:

```php
// In IntegrationController
use App\Events\IntegrationUpdated;

public function store(Request $request)
{
    $integration = Integration::updateOrCreate(...);
    
    event(new IntegrationUpdated($integration));
    
    return response()->json(...);
}

// Event Listener
class ClearIntegrationCache
{
    public function handle(IntegrationUpdated $event)
    {
        $integration = $event->integration;
        Cache::forget("integration_{$integration->integration_type}");
    }
}
```

---

## Console Commands

Create artisan commands for integration management:

```php
// Check integration status
php artisan integration:status

// Test specific integration
php artisan integration:test email

// Clear integration cache
php artisan integration:cache-clear
```

Example command:

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Integration;

class IntegrationStatus extends Command
{
    protected $signature = 'integration:status';
    protected $description = 'Show status of all integrations';
    
    public function handle()
    {
        $integrations = Integration::all();
        
        $this->table(
            ['Type', 'Provider', 'Status', 'Last Updated'],
            $integrations->map(function ($integration) {
                return [
                    $integration->integration_type,
                    $integration->provider ?? 'N/A',
                    $integration->status ? '✓ Enabled' : '✗ Disabled',
                    $integration->updated_at->diffForHumans()
                ];
            })
        );
    }
}
```

---

## Security Reminders

1. **Never Log Sensitive Data**: Don't log passwords, secrets, or tokens
2. **Use HTTPS**: Always use HTTPS for API calls
3. **Validate Webhooks**: Verify webhook signatures
4. **Rate Limiting**: Implement rate limiting for API calls
5. **Error Messages**: Don't expose sensitive config details in error messages

---

## Summary Checklist

- [ ] Create service classes for each integration
- [ ] Implement configuration caching
- [ ] Add proper error handling
- [ ] Write unit tests
- [ ] Add integration checks in middleware
- [ ] Set up event listeners for cache clearing
- [ ] Create console commands for management
- [ ] Document integration-specific logic
- [ ] Test in development environment
- [ ] Monitor API usage and errors

---

**Version:** 1.0.0  
**Date:** January 23, 2026  
**Maintainer:** Development Team
