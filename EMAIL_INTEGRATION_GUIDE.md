# Email (SMTP) Dynamic Integration Guide

## Overview

The Email (SMTP) service is now **fully integrated with the Admin Integrations module**. All SMTP credentials are managed through the database, and email configuration is automatically loaded on application boot.

## Features Implemented

### ✅ Dynamic Configuration
- All SMTP credentials pulled from `integrations` table
- No hardcoded SMTP settings in `.env` or `config/mail.php`
- Configuration managed through Admin Panel UI
- Real-time enable/disable toggle

### ✅ EmailService Class
- Centralized service for email operations
- Automatic configuration loading on app boot
- Built-in error handling
- Configuration validation

### ✅ Test Email Feature
- Send test email directly from Admin Panel
- Validates SMTP configuration
- Helps troubleshoot email issues
- Available when email integration is enabled

### ✅ Automatic Bootstrap
- Email config loaded in `AppServiceProvider`
- No manual configuration needed
- Works seamlessly with Laravel's Mail facade

---

## Setup Instructions

### Step 1: Configure Email in Admin Panel

1. **Log in to Admin Panel**
2. Navigate to **Settings → Integrations**
3. Find the **Email (SMTP)** card
4. Fill in the following fields:

   - **Mail Driver**: SMTP (default)
   - **SMTP Host**: Your SMTP server (e.g., `smtp.gmail.com`, `smtp.mailtrap.io`)
   - **SMTP Port**: Port number (usually `587` for TLS, `465` for SSL)
   - **Encryption**: Select TLS, SSL, or None
   - **Username / Email**: Your SMTP username or email
   - **Password**: Your SMTP password or app password
   - **From Email Address**: Email address to send from
   - **From Name**: Display name for sent emails
   - **Status**: Toggle to **Enabled**

5. Click **"Save Configuration"**

### Step 2: Test Email Configuration

1. After saving, a **"Send Test Email"** button appears
2. Click the button
3. Enter your email address
4. Check your inbox for the test email

---

## Common SMTP Providers

### Gmail

```
SMTP Host: smtp.gmail.com
SMTP Port: 587
Encryption: TLS
Username: your-email@gmail.com
Password: [App Password - not your Gmail password]
```

**Note**: You need to create an [App Password](https://support.google.com/accounts/answer/185833) for Gmail.

### Mailtrap (Testing)

```
SMTP Host: smtp.mailtrap.io
SMTP Port: 2525
Encryption: TLS
Username: [From Mailtrap Dashboard]
Password: [From Mailtrap Dashboard]
```

### SendGrid

```
SMTP Host: smtp.sendgrid.net
SMTP Port: 587
Encryption: TLS
Username: apikey
Password: [Your SendGrid API Key]
```

### Mailgun

```
SMTP Host: smtp.mailgun.org
SMTP Port: 587
Encryption: TLS
Username: postmaster@your-domain.com
Password: [Your Mailgun SMTP Password]
```

### Amazon SES

```
SMTP Host: email-smtp.us-east-1.amazonaws.com
SMTP Port: 587
Encryption: TLS
Username: [Your SES SMTP Username]
Password: [Your SES SMTP Password]
```

---

## Architecture

### Files Created/Modified

```
app/
├── Services/
│   └── EmailService.php             # Service class for email operations
├── Providers/
│   └── AppServiceProvider.php       # Loads email config on boot
├── Http/Controllers/
│   └── IntegrationController.php    # Added testEmail() method
routes/
└── web.php                           # Added test email route
resources/views/admin/integrations/
└── index.blade.php                   # Added test email button
```

### EmailService Methods

```php
// Check if configured
$emailService->isConfigured(): bool

// Apply configuration to Laravel
$emailService->applyMailConfiguration(): void

// Get configuration values
$emailService->getSmtpHost(): ?string
$emailService->getSmtpPort(): int
$emailService->getEncryption(): ?string
$emailService->getFromEmail(): ?string
$emailService->getFromName(): ?string

// Send test email
$emailService->sendTestEmail($toEmail, $subject, $message): bool

// Refresh configuration from database
$emailService->refresh(): void
```

---

## How It Works

### Application Boot Process

```
1. Laravel starts
   ↓
2. AppServiceProvider::boot() runs
   ↓
3. EmailService loads configuration from database
   ↓
4. If email integration is enabled and configured:
   ↓
5. Email config applied to Laravel's Config
   ↓
6. All Mail::send() calls use database configuration
```

### Configuration Flow

**Before (❌ Hardcoded in .env)**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=user@gmail.com
MAIL_PASSWORD=password
```

**After (✅ Dynamic from Database)**:
```php
// Configuration automatically loaded from integrations table
// No .env entries needed
Mail::to('user@example.com')->send(new OrderConfirmation($order));
```

---

## Usage Examples

### Sending Emails

Once configured, use Laravel's Mail facade as normal:

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

// Send order confirmation
Mail::to($customer->email)->send(new OrderConfirmation($order));

// Send raw email
Mail::raw('Email content here', function ($message) {
    $message->to('user@example.com')
            ->subject('Subject Here');
});

// Send with view
Mail::send('emails.welcome', ['user' => $user], function ($message) use ($user) {
    $message->to($user->email)
            ->subject('Welcome!');
});
```

### Check if Email is Configured

```php
use App\Services\EmailService;

$emailService = app(EmailService::class);

if ($emailService->isConfigured()) {
    // Email is configured and enabled
    Mail::to($user->email)->send(new WelcomeMail());
} else {
    // Email not configured, log or notify admin
    Log::warning('Email service not configured');
}
```

### Manual Configuration Refresh

```php
// If you update email config in database programmatically
$emailService = app(EmailService::class);
$emailService->refresh();
$emailService->applyMailConfiguration();
```

---

## Test Email Feature

### How to Use

1. **Save Email Configuration** in Admin Panel
2. **Enable** the integration (toggle Status to Enabled)
3. Click **"Send Test Email"** button
4. Enter your email address in the prompt
5. Check your inbox

### What Gets Sent

```
Subject: Test Email from [Your App Name]
Body: This is a test email to verify your SMTP configuration is working correctly.
```

### Troubleshooting Test Email

**Email Not Received?**

1. ✓ Check spam/junk folder
2. ✓ Verify SMTP credentials are correct
3. ✓ Check `storage/logs/laravel.log` for errors
4. ✓ Ensure port is not blocked by firewall
5. ✓ For Gmail, use App Password, not regular password

**Common Errors:**

- **Connection Timeout**: Wrong host or port
- **Authentication Failed**: Wrong username/password
- **SSL/TLS Error**: Wrong encryption type
- **Connection Refused**: Port blocked by firewall

---

## Creating Custom Mailable

```php
// Generate mailable
php artisan make:mail OrderShipped

// app/Mail/OrderShipped.php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use App\Models\Order;

class OrderShipped extends Mailable
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject('Your Order Has Shipped!')
                    ->view('emails.orders.shipped');
    }
}

// Usage
use App\Mail\OrderShipped;
Mail::to($order->customer->email)->send(new OrderShipped($order));
```

---

## Email Queue (Optional)

For better performance, queue emails:

```php
// Queue email instead of sending immediately
Mail::to($user->email)->queue(new WelcomeMail());

// Queue with delay
Mail::to($user->email)->later(now()->addMinutes(5), new ReminderMail());
```

**Setup Queue:**
```bash
# In .env
QUEUE_CONNECTION=database

# Create jobs table
php artisan queue:table
php artisan migrate

# Run queue worker
php artisan queue:work
```

---

## Security Best Practices

### 1. Gmail App Passwords

Never use your actual Gmail password. Create an App Password:

1. Go to Google Account → Security
2. Enable 2-Step Verification
3. Search for "App passwords"
4. Generate password for "Mail"
5. Use generated password in Admin Panel

### 2. Encryption

Always use TLS or SSL encryption:
- ✓ **TLS** (Port 587) - Recommended
- ✓ **SSL** (Port 465) - Also secure
- ❌ **None** - Only for local testing

### 3. From Address

Use a legitimate domain email:
- ✓ `noreply@yourdomain.com`
- ✓ `orders@yourdomain.com`
- ❌ `random@gmail.com` (may be marked as spam)

### 4. Rate Limiting

Most SMTP providers have rate limits:
- **Gmail**: 500 emails/day
- **SendGrid**: Based on plan
- **Mailgun**: Based on plan

Monitor usage to avoid hitting limits.

---

## Troubleshooting

### Email Not Sending

**Check Configuration:**
```php
// In tinker or controller
$emailService = app(\App\Services\EmailService::class);
dd($emailService->isConfigured());
```

**Check Logs:**
```bash
tail -f storage/logs/laravel.log | grep -i mail
```

**Test SMTP Connection:**
```bash
# Using telnet (Windows)
telnet smtp.gmail.com 587

# Using openssl
openssl s_client -starttls smtp -connect smtp.gmail.com:587
```

### Common Issues

| Issue | Solution |
|-------|----------|
| Connection timeout | Check host/port, ensure not blocked |
| Auth failed | Verify username/password |
| SSL error | Change encryption type (TLS/SSL/None) |
| Email in spam | Use legitimate from address, setup SPF/DKIM |
| Rate limit | Reduce sending frequency or upgrade plan |

---

## Migration from .env

If you were using `.env` for email:

### Old Way (❌ Don't use)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=user@gmail.com
MAIL_PASSWORD=password
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### New Way (✅ Use this)
1. Copy values from `.env` to Admin Panel
2. Remove from `.env` (optional, but recommended):
   ```env
   # MAIL_MAILER=smtp  # REMOVE
   # MAIL_HOST=smtp.gmail.com  # REMOVE
   # ... etc
   ```
3. Email config now loaded from database automatically

---

## Advanced: Multiple Email Configurations

Currently supports one email configuration. To send from different addresses:

```php
// Option 1: Change "from" for specific email
Mail::to($user->email)
    ->from('sales@yourdomain.com', 'Sales Team')
    ->send(new SalesMail());

// Option 2: Create separate mailer (requires code changes)
// This would require extending the service to support multiple configs
```

---

## Monitoring & Logging

### Email Logs

All email activities are logged:

```bash
# View email logs
tail -f storage/logs/laravel.log | grep "Email"
```

### Log Entries

- **Configuration Loaded**: `Email configuration loaded from database`
- **Test Email Sent**: `Test email sent successfully`
- **Email Failed**: `Failed to send test email`

### Failed Email Queue

If using queues, check failed jobs:

```bash
php artisan queue:failed
php artisan queue:retry [job-id]
```

---

## API for Developers

### Send Email via Controller

```php
namespace App\Http\Controllers;

use App\Services\EmailService;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    protected $emailService;
    
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    
    public function sendWelcomeEmail($userId)
    {
        if (!$this->emailService->isConfigured()) {
            return response()->json([
                'error' => 'Email service not configured'
            ], 503);
        }
        
        $user = User::findOrFail($userId);
        
        Mail::to($user->email)->send(new WelcomeMail($user));
        
        return response()->json([
            'message' => 'Email sent successfully'
        ]);
    }
}
```

---

## FAQs

**Q: Can I use multiple SMTP providers?**
A: Currently one active configuration at a time. Switch in Admin Panel.

**Q: Does this work with Gmail?**
A: Yes! Use App Password instead of regular password.

**Q: Can I test without sending real emails?**
A: Yes, use Mailtrap.io for testing in development.

**Q: What if I disable the integration?**
A: Emails will fail. Check `isConfigured()` before sending.

**Q: Can I schedule emails?**
A: Yes, use Laravel's queued emails with `later()` method.

**Q: How do I add attachments?**
A: Use Laravel's Mailable with `->attach()` method.

---

## Best Practices

1. ✓ **Test before going live** - Always send test email first
2. ✓ **Use App Passwords** for Gmail (never regular password)
3. ✓ **Enable TLS encryption** for security
4. ✓ **Queue bulk emails** for better performance
5. ✓ **Monitor logs** regularly for failed emails
6. ✓ **Setup SPF/DKIM** records for your domain
7. ✓ **Use legitimate from address** to avoid spam filters

---

## Support Resources

- **Laravel Mail Documentation**: https://laravel.com/docs/mail
- **Gmail App Passwords**: https://support.google.com/accounts/answer/185833
- **Mailtrap**: https://mailtrap.io/
- **SendGrid**: https://sendgrid.com/
- **Mailgun**: https://www.mailgun.com/

---

**Version:** 1.0.0  
**Date:** January 23, 2026  
**Integration Type:** Dynamic Database Configuration
