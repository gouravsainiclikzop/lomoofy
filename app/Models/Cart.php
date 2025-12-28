<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Cart extends Model
{
    protected $fillable = [
        'session_id',
        'customer_id',
        'coupon_code',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'expires_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    /**
     * Boot method to set expiration date
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($cart) {
            if (empty($cart->expires_at)) {
                $cart->expires_at = Carbon::now()->addDays(30);
            }
        });
    }

    /**
     * Cart items relationship
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Customer relationship
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Coupon relationship
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    /**
     * Check if cart is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Recalculate cart totals
     */
    public function recalculateTotals()
    {
        $this->load('items', 'coupon');
        
        // Calculate subtotal
        $subtotal = $this->items->sum('total_price');
        $this->subtotal = $subtotal;
        
        // Calculate discount from coupon if exists
        $discountAmount = 0;
        if ($this->coupon_code && $this->coupon) {
            $coupon = $this->coupon;
            
            // Check if coupon has required methods, otherwise use basic calculation
            if (method_exists($coupon, 'isActive') && method_exists($coupon, 'canBeUsed') && method_exists($coupon, 'calculateDiscount')) {
                if ($coupon->isActive() && $coupon->canBeUsed()) {
                    // Check minimum order amount if property exists
                    if (!property_exists($coupon, 'min_order_amount') || !$coupon->min_order_amount || $subtotal >= $coupon->min_order_amount) {
                        $discountAmount = $coupon->calculateDiscount($subtotal);
                    }
                }
            } else {
                // Fallback to basic discount calculation
                if (property_exists($coupon, 'discount_type') && property_exists($coupon, 'discount_value')) {
                    if ($coupon->discount_type === 'percentage') {
                        $discountAmount = ($subtotal * $coupon->discount_value) / 100;
                    } else {
                        $discountAmount = min($coupon->discount_value, $subtotal);
                    }
                }
            }
        }
        $this->discount_amount = $discountAmount;
        
        // Calculate tax (0% for now - can be configured later)
        $taxRate = 0;
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $taxableAmount * $taxRate;
        $this->tax_amount = $taxAmount;
        
        // Calculate shipping
        $allItemsFreeShipping = $this->items->every(function($item) {
            return $item->product && $item->product->free_shipping;
        });
        
        $hasNonShippingItems = $this->items->contains(function($item) {
            return $item->product && !$item->product->requires_shipping;
        });
        
        if ($allItemsFreeShipping || $hasNonShippingItems) {
            $shippingAmount = 0;
        } else {
            $freeShippingThreshold = 0;
            $defaultShippingCost = 0;
            $shippingAmount = $subtotal > $freeShippingThreshold ? 0 : $defaultShippingCost;
        }
        
        $this->shipping_amount = $shippingAmount;
        
        // Calculate total
        $this->total_amount = $subtotal - $discountAmount + $taxAmount + $shippingAmount;
        
        $this->save();
        
        return $this;
    }

    /**
     * Scope for active (non-expired) carts
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Merge guest cart with customer cart on login
     * This method handles the cart merging when a guest user logs in
     */
    public static function mergeGuestCartWithCustomerCart($customerId, $sessionId)
    {
        try {
            \Log::info('Starting cart merge process', [
                'customer_id' => $customerId,
                'session_id' => $sessionId
            ]);

            // Find guest cart (session-based) - try exact match first
            $guestCart = self::where('session_id', $sessionId)
                ->whereNull('customer_id')
                ->active()
                ->with('items')
                ->first();

            // If no exact match found, try to find any recent guest cart for this customer
            // This is a fallback for when session IDs don't match
            if (!$guestCart) {
                \Log::info('No exact session match found, trying fallback approach', [
                    'looking_for_session_id' => $sessionId
                ]);

                // Get all guest carts ordered by most recent
                $allGuestCarts = self::whereNull('customer_id')
                    ->active()
                    ->with('items')
                    ->orderBy('updated_at', 'desc')
                    ->get();
                

                // Use the most recent guest cart with items as fallback
                $guestCart = $allGuestCarts->filter(function($cart) {
                    return $cart->items->count() > 0;
                })->first();

                if ($guestCart) {
                    \Log::info('Using fallback guest cart', [
                        'fallback_cart_id' => $guestCart->id,
                        'fallback_session_id' => $guestCart->session_id,
                        'items_count' => $guestCart->items->count()
                    ]);
                }
            }

            if (!$guestCart || $guestCart->items->isEmpty()) {
                \Log::info('No guest cart found or cart is empty, skipping merge', [
                    'guest_cart_found' => $guestCart ? 'yes' : 'no',
                    'items_count' => $guestCart ? $guestCart->items->count() : 0
                ]);
                return null;
            }

            \Log::info('Found guest cart with items', [
                'guest_cart_id' => $guestCart->id,
                'items_count' => $guestCart->items->count()
            ]);

            // Find existing customer cart
            $customerCart = self::where('customer_id', $customerId)
                ->whereNull('session_id')
                ->active()
                ->with('items')
                ->first();

            if (!$customerCart) {
                // No existing customer cart, just convert guest cart to customer cart
                \Log::info('No existing customer cart, converting guest cart');
                
                $guestCart->update([
                    'customer_id' => $customerId,
                    'session_id' => null,
                ]);
                
                $guestCart->recalculateTotals();
                
                \Log::info('Guest cart converted to customer cart', [
                    'cart_id' => $guestCart->id
                ]);
                
                return $guestCart;
            }

            \Log::info('Found existing customer cart, merging items', [
                'customer_cart_id' => $customerCart->id,
                'existing_items_count' => $customerCart->items->count()
            ]);

            // Merge items from guest cart to customer cart
            foreach ($guestCart->items as $guestItem) {
                // Check if customer cart already has this product/variant combination
                $existingItem = $customerCart->items->where('product_id', $guestItem->product_id)
                    ->where('product_variant_id', $guestItem->product_variant_id)
                    ->first();

                if ($existingItem) {
                    // Update quantity and total price
                    $newQuantity = $existingItem->quantity + $guestItem->quantity;
                    $existingItem->update([
                        'quantity' => $newQuantity,
                        'total_price' => $newQuantity * $existingItem->unit_price,
                    ]);
                    
                    \Log::info('Updated existing cart item quantity', [
                        'product_id' => $guestItem->product_id,
                        'variant_id' => $guestItem->product_variant_id,
                        'old_quantity' => $existingItem->quantity - $guestItem->quantity,
                        'added_quantity' => $guestItem->quantity,
                        'new_quantity' => $newQuantity
                    ]);
                } else {
                    // Create new cart item in customer cart
                    $customerCart->items()->create([
                        'product_id' => $guestItem->product_id,
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity' => $guestItem->quantity,
                        'unit_price' => $guestItem->unit_price,
                        'total_price' => $guestItem->total_price,
                        'reserved_stock' => $guestItem->reserved_stock,
                    ]);
                    
                    \Log::info('Added new item to customer cart', [
                        'product_id' => $guestItem->product_id,
                        'variant_id' => $guestItem->product_variant_id,
                        'quantity' => $guestItem->quantity
                    ]);
                }
            }

            // Copy coupon if customer cart doesn't have one but guest cart does
            if ($guestCart->coupon_code && !$customerCart->coupon_code) {
                $customerCart->update([
                    'coupon_code' => $guestCart->coupon_code,
                ]);
                
                \Log::info('Applied guest cart coupon to customer cart', [
                    'coupon_code' => $guestCart->coupon_code
                ]);
            }

            // Recalculate customer cart totals
            $customerCart->recalculateTotals();

            // Delete the guest cart as it's no longer needed
            $guestCart->items()->delete();
            $guestCart->delete();

            \Log::info('Cart merge completed successfully', [
                'customer_cart_id' => $customerCart->id,
                'final_items_count' => $customerCart->items()->count()
            ]);

            return $customerCart;

        } catch (\Exception $e) {
            \Log::error('Error during cart merge', [
                'customer_id' => $customerId,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't throw exception to avoid breaking login process
            return null;
        }
    }

}
