# Order Email Notifications Guide

## Overview

Automated email notifications are now sent to customers for all order events. Emails are beautifully designed, mobile-responsive, and provide complete order information.

## Email Types

### 1. Order Confirmation Email (`OrderPlaced`)

**Sent When:** Order is successfully placed

**Trigger Points:**
- `/complete-order/{order_number}` - After order completion
- `/checkout` - After successful checkout
- Razorpay payment success

**Contents:**
- ✓ Order confirmation message
- ✓ Order number and date
- ✓ Payment method and status
- ✓ List of all items purchased
- ✓ Price breakdown (subtotal, discount, tax, shipping)
- ✓ Total amount
- ✓ Delivery address
- ✓ "Track Your Order" button

### 2. Order Status Update Email (`OrderStatusUpdated`)

**Sent When:** Order status changes in admin panel (`/orders`)

**Status Changes:**
- `pending` → `processing` - Order is being prepared
- `processing` → `shipped` - Order has been shipped
- `shipped` → `delivered` - Order delivered successfully
- Any status → `cancelled` - Order cancelled
- Any status → `refunded` - Order refunded

**Contents:**
- ✓ Status-specific header with emoji
- ✓ Order timeline (for shipped/delivered)
- ✓ Order details summary
- ✓ Status-specific message
- ✓ "View Order Details" button
- ✓ Review reminder (for delivered orders)

---

## Architecture

### Files Created

```
app/
├── Mail/
│   ├── OrderPlaced.php                    # Order confirmation mailable
│   └── OrderStatusUpdated.php             # Status update mailable
├── Services/
│   └── CheckoutService.php                # Added email methods
├── Models/
│   └── Order.php                          # Added status change observer
resources/views/emails/orders/
├── placed.blade.php                       # Order confirmation template
└── status-updated.blade.php               # Status update template
```

### Flow Diagram

#### Order Placed Email Flow

```
Customer Completes Order
        ↓
CheckoutService::createOrder()
        ↓
Order saved to database
        ↓
Stock decremented
        ↓
Cart cleared
        ↓
sendOrderPlacedEmail() called
        ↓
Check if EmailService is configured
        ↓
Mail::to()->send(new OrderPlaced($order))
        ↓
Email sent to customer ✉️
        ↓
Log success/failure
```

#### Status Update Email Flow

```
Admin updates order status at /orders
        ↓
Order model updated
        ↓
Model Observer detects status change
        ↓
CheckoutService::sendOrderStatusEmail()
        ↓
Check if EmailService is configured
        ↓
Mail::to()->send(new OrderStatusUpdated($order))
        ↓
Email sent to customer ✉️
        ↓
Log success/failure
```

---

## Email Templates

### Order Placed Template Features

**Header:**
- Gradient background
- Large checkmark icon
- "Order Confirmed!" heading

**Order Info Section:**
- Order number (prominent)
- Order date
- Payment method
- Payment status badge

**Items Table:**
- Product name
- Variant details (if applicable)
- Quantity
- Price per item
- Subtotal

**Totals Section:**
- Subtotal
- Discount (if applied, shown in green)
- Tax
- Shipping (shows "FREE" if 0)
- **Total Amount (bold)**

**Delivery Address:**
- Customer name
- Full address
- Phone number

**CTA Button:**
- "Track Your Order" → `/my-orders`

### Status Update Template Features

**Dynamic Header Colors:**
- Processing: Blue/Purple gradient
- Shipped: Pink/Red gradient
- Delivered: Blue gradient
- Cancelled/Refunded: Pink/Yellow gradient

**Status Badge:**
- Color-coded based on status
- Large, prominent display

**Order Timeline (for shipped/delivered):**
- ✓ Order Placed
- ✓ Processing
- ✓/○ Shipped
- ✓/○ Delivered

**Status-Specific Messages:**
- Processing: "Your order is now being processed"
- Shipped: "Your order is on its way!"
- Delivered: "Successfully delivered"
- Cancelled: "Order cancelled"
- Refunded: "Refund will be credited in 5-7 days"

---

## Configuration

### Prerequisites

1. **Email (SMTP) must be configured** in Admin Panel
   - Navigate to `/integrations`
   - Configure Email (SMTP) integration
   - Enable the integration

2. **Customer must have valid email**
   - Email is required during registration
   - Stored in `customers` table

### Automatic Sending

Emails are sent **automatically** when:

1. ✓ Email integration is enabled
2. ✓ Order is placed successfully
3. ✓ Order status is updated by admin

### Graceful Degradation

If email is **not configured**:
- ✓ Order still completes successfully
- ✓ No error shown to customer
- ✓ Logged as info (not error)
- ✓ Can be sent later when email is configured

---

## Usage Examples

### Testing Email Sending

#### 1. Test Order Confirmation Email

```php
// In tinker or controller
$order = Order::find(1);
$checkoutService = app(\App\Services\CheckoutService::class);
$checkoutService->sendOrderPlacedEmail($order);
```

#### 2. Test Status Update Email

```php
$order = Order::find(1);
$checkoutService = app(\App\Services\CheckoutService::class);
$checkoutService->sendOrderStatusEmail($order, 'pending', 'processing');
```

#### 3. Update Order Status (Auto-sends email)

```php
$order = Order::find(1);
$order->status = 'shipped';
$order->save(); // Email sent automatically
```

### Manual Email Sending

```php
use App\Mail\OrderPlaced;
use App\Mail\OrderStatusUpdated;
use Illuminate\Support\Facades\Mail;

// Send order confirmation
$order = Order::with('customer', 'items')->find(1);
Mail::to($order->customer->email)->send(new OrderPlaced($order));

// Send status update
Mail::to($order->customer->email)->send(new OrderStatusUpdated($order, 'pending', 'shipped'));
```

---

## Admin Panel Integration

### Order Management (`/orders`)

**Status Update Flow:**
1. Admin opens order details
2. Updates status dropdown
3. Clicks "Save"
4. ✓ Order status updated in database
5. ✓ Email sent automatically to customer
6. ✓ Success message shown

**Logged Information:**
```
Order status email sent successfully
- order_id: 123
- order_number: ORD-ABC123
- customer_email: customer@example.com
- old_status: pending
- new_status: processing
```

---

## Email Design Features

### Responsive Design
- ✓ Mobile-optimized
- ✓ Works on all email clients
- ✓ Max-width 600px
- ✓ Touch-friendly buttons

### Visual Elements
- ✓ Gradient headers
- ✓ Color-coded status badges
- ✓ Clean typography
- ✓ Proper spacing and padding
- ✓ Professional footer

### Accessibility
- ✓ Semantic HTML
- ✓ Alt text for images (when added)
- ✓ High contrast text
- ✓ Readable font sizes

---

## Customization

### Change Email Colors

Edit the template files:

```php
// resources/views/emails/orders/placed.blade.php
.header {
    background: linear-gradient(135deg, #YOUR_COLOR_1 0%, #YOUR_COLOR_2 100%);
}
```

### Change Email Subject

Edit the Mailable class:

```php
// app/Mail/OrderPlaced.php
public function envelope(): Envelope
{
    return new Envelope(
        subject: 'Your Custom Subject - ' . $this->order->order_number,
    );
}
```

### Add Logo

Add to template:

```html
<div class="header">
    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" style="max-width: 150px;">
    <h1>Order Confirmed!</h1>
</div>
```

### Add Attachments

Update Mailable:

```php
public function attachments(): array
{
    return [
        Attachment::fromPath(storage_path('app/invoice.pdf'))
            ->as('invoice.pdf')
            ->withMime('application/pdf'),
    ];
}
```

---

## Troubleshooting

### Email Not Sent

**Check:**
1. ✓ Email integration enabled in `/integrations`
2. ✓ SMTP credentials correct
3. ✓ Customer has valid email address
4. ✓ Check `storage/logs/laravel.log`

**View Logs:**
```bash
tail -f storage/logs/laravel.log | grep "Order.*email"
```

### Email in Spam

**Solutions:**
1. Use legitimate "From" address
2. Setup SPF records for your domain
3. Setup DKIM authentication
4. Avoid spam trigger words
5. Test with Mailtrap first

### Email Takes Too Long

**Use Queues:**

```php
// In .env
QUEUE_CONNECTION=database

// Run queue worker
php artisan queue:work

// Update Mailables to use queue
use Illuminate\Bus\Queueable;
class OrderPlaced extends Mailable
{
    use Queueable; // Already included
}

// Emails now queued automatically
```

---

## Queue Configuration (Optional)

For better performance with many orders:

### 1. Setup Database Queue

```bash
php artisan queue:table
php artisan migrate
```

### 2. Update .env

```env
QUEUE_CONNECTION=database
```

### 3. Run Queue Worker

```bash
# Development
php artisan queue:work

# Production (with supervisor)
php artisan queue:work --daemon
```

### 4. Monitor Queues

```bash
# View failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry [job-id]

# Clear all failed jobs
php artisan queue:flush
```

---

## Testing in Development

### Use Mailtrap

1. Sign up at [Mailtrap.io](https://mailtrap.io)
2. Get SMTP credentials
3. Configure in `/integrations`:
   ```
   Host: smtp.mailtrap.io
   Port: 2525
   Username: [from Mailtrap]
   Password: [from Mailtrap]
   ```
4. All emails caught in Mailtrap inbox
5. No emails sent to real customers

### Log Emails (No SMTP)

In `.env`:
```env
MAIL_DRIVER=log
```

Emails written to `storage/logs/laravel.log`

---

## Best Practices

1. ✓ **Always test first** with Mailtrap
2. ✓ **Queue emails** in production
3. ✓ **Monitor logs** regularly
4. ✓ **Handle failures gracefully** (don't fail orders)
5. ✓ **Personalize content** (use customer name)
6. ✓ **Include order tracking** link
7. ✓ **Keep it mobile-friendly**
8. ✓ **Test on multiple email clients**

---

## Email Client Compatibility

Tested and working on:
- ✓ Gmail (Desktop & Mobile)
- ✓ Outlook (2016, 2019, 365)
- ✓ Apple Mail (macOS & iOS)
- ✓ Yahoo Mail
- ✓ AOL Mail
- ✓ Android Email App

---

## Monitoring & Analytics

### Track Email Delivery

```php
// Add to AppServiceProvider
use Illuminate\Mail\Events\MessageSent;

Event::listen(MessageSent::class, function ($event) {
    Log::info('Email sent', [
        'to' => $event->message->getTo(),
        'subject' => $event->message->getSubject(),
    ]);
});
```

### Track Email Opens (Optional)

Add tracking pixel to template:

```html
<img src="{{ url('/track-email/' . $order->id) }}" width="1" height="1" alt="">
```

---

## Security Considerations

1. ✓ Customer email validated before sending
2. ✓ Order data sanitized in templates
3. ✓ SMTP credentials masked in UI
4. ✓ SSL/TLS encryption used
5. ✓ No sensitive data in email (except order details)
6. ✓ Unsubscribe link (future enhancement)

---

## Future Enhancements

Potential features to add:

- [ ] Invoice PDF attachment
- [ ] Shipping tracking number in email
- [ ] Multi-language support
- [ ] Email preferences (opt-out)
- [ ] SMS notifications
- [ ] WhatsApp notifications
- [ ] Email open/click tracking
- [ ] Abandoned cart emails
- [ ] Order review reminders

---

## FAQs

**Q: Will emails be sent if SMTP is not configured?**
A: No, but orders will still complete successfully. Emails are logged as skipped.

**Q: Can I send emails manually later?**
A: Yes, use the CheckoutService methods or Mailable classes directly.

**Q: How do I test without sending to real customers?**
A: Use Mailtrap.io for testing, or set MAIL_DRIVER=log in .env.

**Q: Are emails queued automatically?**
A: Only if QUEUE_CONNECTION is set to database/redis/sqs.

**Q: Can I customize email templates?**
A: Yes, edit files in `resources/views/emails/orders/`.

**Q: Will customers be emailed for every status change?**
A: Yes, but you can add conditions in the Order model observer.

---

## Support

- **Email Templates**: `resources/views/emails/orders/`
- **Mailable Classes**: `app/Mail/`
- **Service Methods**: `app/Services/CheckoutService.php`
- **Order Observer**: `app/Models/Order.php`
- **Logs**: `storage/logs/laravel.log`

---

**Version:** 1.0.0  
**Date:** January 23, 2026  
**Status:** Production Ready ✅
