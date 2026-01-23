<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\CheckoutService;

class Order extends Model
{
    use SoftDeletes;

    /**
     * Track original status for email notifications
     */
    protected $originalStatus;

    protected $fillable = [
        'order_number',
        'source',
        'customer_id',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'shipping_zone_id',
        'shipping_method_id',
        'shipping_rate_id',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'notes',
        'shipping_address',
        'billing_address',
        'billing_same_as_shipping',
        'shipping_address_id',
        'billing_address_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'billing_same_as_shipping' => 'boolean',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingZone()
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function shippingRate()
    {
        return $this->belongsTo(ShippingRate::class);
    }

    // Generate unique order number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });

        // Track status changes for email notifications
        static::updating(function ($order) {
            if ($order->isDirty('status')) {
                $order->originalStatus = $order->getOriginal('status');
            }
        });

        static::updated(function ($order) {
            // Send email if status changed
            if (isset($order->originalStatus) && $order->originalStatus !== $order->status) {
                try {
                    $checkoutService = app(CheckoutService::class);
                    $checkoutService->sendOrderStatusEmail($order, $order->originalStatus, $order->status);
                } catch (\Exception $e) {
                    \Log::error('Failed to send order status email in model observer', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
    }
}
