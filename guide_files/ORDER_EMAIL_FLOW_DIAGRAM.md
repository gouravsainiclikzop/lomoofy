# Order Email Notification Flow Diagrams

## System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                     ORDER EMAIL NOTIFICATION SYSTEM                  │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌─────────────────┐      ┌──────────────────┐                     │
│  │   Frontend      │      │  Admin Panel     │                     │
│  │  /complete-order│      │   /orders        │                     │
│  └────────┬────────┘      └────────┬─────────┘                     │
│           │                        │                                │
│           ▼                        ▼                                │
│  ┌─────────────────────────────────────────────┐                   │
│  │         CheckoutService / Order Model        │                   │
│  └────────┬────────────────────────┬────────────┘                   │
│           │                        │                                │
│           ▼                        ▼                                │
│  ┌──────────────────┐    ┌──────────────────┐                     │
│  │ sendOrderPlaced  │    │ sendStatusEmail  │                     │
│  │     Email()      │    │      ()          │                     │
│  └────────┬─────────┘    └────────┬─────────┘                     │
│           │                        │                                │
│           └────────────┬───────────┘                                │
│                        ▼                                            │
│              ┌───────────────────┐                                  │
│              │  EmailService     │                                  │
│              │  (Check Config)   │                                  │
│              └─────────┬─────────┘                                  │
│                        ▼                                            │
│              ┌───────────────────┐                                  │
│              │  Mail Facade      │                                  │
│              │  (Laravel Mail)   │                                  │
│              └─────────┬─────────┘                                  │
│                        ▼                                            │
│              ┌───────────────────┐                                  │
│              │  SMTP Server      │                                  │
│              │  (Email Provider) │                                  │
│              └─────────┬─────────┘                                  │
│                        ▼                                            │
│              ┌───────────────────┐                                  │
│              │  Customer Inbox   │                                  │
│              │       📧          │                                  │
│              └───────────────────┘                                  │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Flow 1: Order Placed Email

### User Journey

```
Customer Cart → Checkout → Complete Order → Email Sent
```

### Detailed Flow

```
┌──────────────────────────────────────────────────────────────────────┐
│                        ORDER PLACED EMAIL FLOW                        │
└──────────────────────────────────────────────────────────────────────┘

1. Customer completes order
   ↓
   http://localhost:8000/complete-order/ORD-XXXXX
   ↓
   
2. FrontendController::completeOrder()
   ↓
   • Validates order
   • Processes payment (if applicable)
   ↓
   
3. CheckoutService::createOrder()
   ↓
   ┌─────────────────────────────────────────┐
   │  DB::transaction(function() {           │
   │    • Create Order record                │
   │    • Save shipping/billing addresses    │
   │    • Create order items                 │
   │    • Decrement product stock            │
   │    • Clear customer cart                │
   │  })                                     │
   └─────────────────────────────────────────┘
   ↓
   
4. Order saved successfully
   ↓
   
5. CheckoutService::sendOrderPlacedEmail($order)
   ↓
   ┌─────────────────────────────────────────┐
   │  try {                                  │
   │    • Get EmailService instance          │
   │    • Check if configured                │
   │    • Mail::to()->send(OrderPlaced)      │
   │    • Log success                        │
   │  } catch {                              │
   │    • Log error                          │
   │    • Don't fail order                   │
   │  }                                      │
   └─────────────────────────────────────────┘
   ↓
   
6. EmailService::isConfigured()
   ↓
   • Query: Integration::byType('email', 'smtp')->active()->first()
   • If found: return true
   • If not found: Log warning, skip email
   ↓
   
7. Mail::to($customer->email)->send(new OrderPlaced($order))
   ↓
   
8. OrderPlaced Mailable
   ↓
   ┌─────────────────────────────────────────┐
   │  • Load order with items & customer     │
   │  • Render email template:               │
   │    - Order confirmation header          │
   │    - Order details                      │
   │    - Items table                        │
   │    - Price breakdown                    │
   │    - Delivery address                   │
   │    - Track order button                 │
   └─────────────────────────────────────────┘
   ↓
   
9. Laravel Mail System
   ↓
   • Get SMTP config from EmailService
   • Connect to SMTP server
   • Send email
   ↓
   
10. Customer receives email 📧
    ↓
    ✅ Order Confirmation Email Delivered
```

---

## Flow 2: Status Update Email

### User Journey

```
Admin Panel → Orders → Update Status → Email Sent
```

### Detailed Flow

```
┌──────────────────────────────────────────────────────────────────────┐
│                    STATUS UPDATE EMAIL FLOW                           │
└──────────────────────────────────────────────────────────────────────┘

1. Admin opens order in /orders
   ↓
   
2. Admin changes order status
   ↓
   • Dropdown: pending → processing
   ↓
   
3. Admin clicks "Save" / "Update"
   ↓
   
4. OrderController::update($id)
   ↓
   $order = Order::find($id);
   $order->status = 'processing';
   $order->save(); ← TRIGGERS MODEL OBSERVER
   ↓
   
5. Order Model Boot Method - Observers
   ↓
   ┌─────────────────────────────────────────┐
   │  static::updating(function($order) {    │
   │    if ($order->isDirty('status')) {     │
   │      $order->originalStatus =           │
   │        $order->getOriginal('status');   │
   │    }                                    │
   │  })                                     │
   └─────────────────────────────────────────┘
   ↓
   Order saved to database
   ↓
   ┌─────────────────────────────────────────┐
   │  static::updated(function($order) {     │
   │    if (status changed) {                │
   │      try {                              │
   │        $service = app(CheckoutService); │
   │        $service->sendOrderStatusEmail(  │
   │          $order,                        │
   │          $oldStatus,                    │
   │          $newStatus                     │
   │        );                               │
   │      } catch { log error }              │
   │    }                                    │
   │  })                                     │
   └─────────────────────────────────────────┘
   ↓
   
6. CheckoutService::sendOrderStatusEmail($order, 'pending', 'processing')
   ↓
   ┌─────────────────────────────────────────┐
   │  try {                                  │
   │    • Get EmailService instance          │
   │    • Check if configured                │
   │    • Mail::to()->send(                  │
   │        OrderStatusUpdated               │
   │      )                                  │
   │    • Log success                        │
   │  } catch {                              │
   │    • Log error                          │
   │    • Don't fail update                  │
   │  }                                      │
   └─────────────────────────────────────────┘
   ↓
   
7. EmailService::isConfigured()
   ↓
   • Query: Integration::byType('email', 'smtp')->active()->first()
   • If found: return true
   • If not found: Log warning, skip email
   ↓
   
8. Mail::to($customer->email)->send(new OrderStatusUpdated($order, $old, $new))
   ↓
   
9. OrderStatusUpdated Mailable
   ↓
   ┌─────────────────────────────────────────┐
   │  • Load order with customer             │
   │  • Determine status header:             │
   │    - processing: 📦 Blue gradient       │
   │    - shipped: 🚚 Pink gradient          │
   │    - delivered: ✅ Blue gradient        │
   │    - cancelled: ❌ Yellow gradient      │
   │  • Render email template:               │
   │    - Status-specific header             │
   │    - Order timeline (if shipped)        │
   │    - Order summary                      │
   │    - Status message                     │
   │    - View order button                  │
   └─────────────────────────────────────────┘
   ↓
   
10. Laravel Mail System
    ↓
    • Get SMTP config from EmailService
    • Connect to SMTP server
    • Send email
    ↓
    
11. Customer receives email 📧
    ↓
    ✅ Status Update Email Delivered
```

---

## Component Interaction Diagram

```
┌────────────────────────────────────────────────────────────────────┐
│                      COMPONENT INTERACTIONS                         │
└────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│  Controller  │ (FrontendController, OrderController)
│  /Frontend   │
└──────┬───────┘
       │ calls
       ▼
┌──────────────────┐
│ CheckoutService  │ (Business Logic)
│                  │
│ • createOrder()  │───┐
│ • sendEmail()    │   │ creates
└──────────────────┘   │
                       ▼
                ┌──────────────┐
                │ Order Model  │ (Database)
                │              │
                │ • boot()     │─┐ observes
                │ • updating() │ │ changes
                │ • updated()  │ │
                └──────────────┘ │
                       │         │
                       │ reads   │ triggers
                       ▼         │
                ┌──────────────────┐
                │  EmailService    │ (Config Loader)
                │                  │
                │ • isConfigured() │
                │ • loadConfig()   │
                └────────┬─────────┘
                         │ queries
                         ▼
                  ┌────────────────┐
                  │  Integration   │ (DB Model)
                  │     Model      │
                  │                │
                  │ • SMTP Config  │
                  └────────────────┘
                         │
       ┌─────────────────┴─────────────────┐
       │                                   │
       ▼                                   ▼
┌──────────────┐                   ┌──────────────┐
│  Mailable    │                   │  Mail Facade │
│  Classes     │                   │   (Laravel)  │
│              │                   │              │
│ • OrderPlaced│───── sends to ───>│ • SMTP       │
│ • StatusUpdt │                   │ • Config     │
└──────────────┘                   └──────┬───────┘
                                          │
                                          ▼
                                   ┌──────────────┐
                                   │ Email Server │
                                   │   (SMTP)     │
                                   └──────┬───────┘
                                          │
                                          ▼
                                   ┌──────────────┐
                                   │   Customer   │
                                   │    Inbox     │
                                   │      📧      │
                                   └──────────────┘
```

---

## Database Schema Relationships

```
┌──────────────────────────────────────────────────────────────────────┐
│                     DATABASE RELATIONSHIPS                            │
└──────────────────────────────────────────────────────────────────────┘

┌─────────────────┐
│   customers     │
│ ───────────────│
│ • id            │
│ • email         │◄────┐
│ • full_name     │     │
│ • phone         │     │
└─────────────────┘     │
                        │ has many
┌─────────────────┐     │
│   orders        │─────┘
│ ───────────────│
│ • id            │
│ • order_number  │
│ • customer_id   │ (FK)
│ • status        │◄──── WATCHED BY MODEL OBSERVER
│ • subtotal      │
│ • tax_amount    │
│ • shipping_amt  │
│ • discount_amt  │
│ • total_amount  │
│ • payment_status│
│ • shipping_addr │ (JSON)
│ • billing_addr  │ (JSON)
└────────┬────────┘
         │ has many
         ▼
┌─────────────────┐
│  order_items    │
│ ───────────────│
│ • id            │
│ • order_id      │ (FK)
│ • product_id    │ (FK)
│ • variant_id    │ (FK)
│ • product_name  │
│ • variant_name  │
│ • quantity      │
│ • price         │
│ • total_price   │
└─────────────────┘

┌─────────────────┐
│ integrations    │
│ ───────────────│
│ • id            │
│ • type          │ ('email')
│ • provider      │ ('smtp')
│ • configuration │ (JSON - encrypted)
│ • status        │ (boolean)
└─────────────────┘
         │
         │ used by
         ▼
   EmailService
         │
         │ provides config to
         ▼
   Mail System → Email Sent
```

---

## Status Transition Matrix

```
┌──────────────────────────────────────────────────────────────────────┐
│                    STATUS TRANSITIONS & EMAILS                        │
└──────────────────────────────────────────────────────────────────────┘

From       →  To          │  Email Sent?  │  Header       │  Icon
────────────────────────────────────────────────────────────────────────
NULL       →  pending     │  ✅ YES       │  Confirmed    │  ✓
pending    →  processing  │  ✅ YES       │  Processing   │  📦
processing →  shipped     │  ✅ YES       │  Shipped      │  🚚
shipped    →  delivered   │  ✅ YES       │  Delivered    │  ✅
any        →  cancelled   │  ✅ YES       │  Cancelled    │  ❌
any        →  refunded    │  ✅ YES       │  Refunded     │  💰
────────────────────────────────────────────────────────────────────────

Timeline Shown For:
• shipped, delivered

Review Request Shown For:
• delivered
```

---

## Error Handling Flow

```
┌──────────────────────────────────────────────────────────────────────┐
│                       ERROR HANDLING                                  │
└──────────────────────────────────────────────────────────────────────┘

Order Created/Updated
        ↓
    Try Send Email
        │
        ├─► Email Service Configured? ──NO──> Log: "Email not configured"
        │                                      Skip email, Continue ✓
        │
        └─► YES
            ↓
        Try Send Mail
            │
            ├─► SMTP Error ──> Catch Exception
            │                  Log: "Failed to send email"
            │                  Store error details
            │                  Continue ✓
            │
            ├─► Network Error ──> Catch Exception
            │                     Log: "Network failure"
            │                     Continue ✓
            │
            └─► Success ──> Log: "Email sent successfully"
                           Continue ✓

Result: ORDER ALWAYS COMPLETES ✅
        Email failure NEVER blocks order
```

---

## Configuration Loading Flow

```
┌──────────────────────────────────────────────────────────────────────┐
│                    CONFIGURATION LOADING                              │
└──────────────────────────────────────────────────────────────────────┘

Application Boot
        ↓
AppServiceProvider::boot()
        ↓
EmailService::loadEmailConfiguration()
        ↓
Query Database:
  Integration::byType('email', 'smtp')
               ->active()
               ->first()
        │
        ├─► Found ──> Extract Configuration (JSON)
        │            Decrypt Sensitive Fields
        │            Load into Laravel Config:
        │              • Config::set('mail.mailers.smtp.host', ...)
        │              • Config::set('mail.mailers.smtp.port', ...)
        │              • Config::set('mail.mailers.smtp.username', ...)
        │              • Config::set('mail.mailers.smtp.password', ...)
        │              • Config::set('mail.from.address', ...)
        │              • Config::set('mail.from.name', ...)
        │            Log: "Email config loaded from database"
        │            ✅ Ready to Send Emails
        │
        └─► Not Found ──> Fall back to .env values
                         Log: "Using .env email config"
                         ⚠️ May not send emails
```

---

## Testing Flow

```
┌──────────────────────────────────────────────────────────────────────┐
│                         TESTING WORKFLOW                              │
└──────────────────────────────────────────────────────────────────────┘

1. Configure Email in Admin
   http://localhost:8000/integrations
        ↓
   Use Mailtrap.io:
   • Host: smtp.mailtrap.io
   • Port: 2525
   • Username: [from mailtrap]
   • Password: [from mailtrap]
        ↓
   Click "Test Email" ──> Verify in Mailtrap Inbox
        ↓
   ✅ Email Working

2. Test Order Placed Email
   http://localhost:8000/complete-order/ORD-XXX
        ↓
   Complete Test Order
        ↓
   Check Mailtrap Inbox
        ↓
   Verify:
   • ✅ Order number correct
   • ✅ Items list correct
   • ✅ Prices correct
   • ✅ Address correct
   • ✅ Styling looks good

3. Test Status Update Email
   http://localhost:8000/orders
        ↓
   Open Order → Change Status → Save
        ↓
   Check Mailtrap Inbox
        ↓
   Verify:
   • ✅ Status header correct
   • ✅ Timeline displayed
   • ✅ Message appropriate
   • ✅ Styling looks good

4. Production Deploy
   Update integrations with real SMTP
   (e.g., Gmail, SendGrid, AWS SES)
        ↓
   ✅ Live Email Notifications
```

---

## Performance Considerations

```
┌──────────────────────────────────────────────────────────────────────┐
│                        PERFORMANCE FLOW                               │
└──────────────────────────────────────────────────────────────────────┘

Without Queues (Current):
Order Created → Send Email (3-5s) → Response to User
Total Time: 5-10 seconds

With Queues (Recommended for Production):
Order Created → Queue Email Job (< 100ms) → Response to User
              ↓
         Background Worker
              ↓
         Send Email (3-5s)
Total Time for User: < 1 second ✨

Setup Queues:
1. php artisan queue:table
2. php artisan migrate
3. Update .env: QUEUE_CONNECTION=database
4. Run: php artisan queue:work
5. Emails now queued automatically (Queueable trait)
```

---

**Comprehensive flow coverage for order email notifications system** ✅

