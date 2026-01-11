<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku', // Required at variant level
        'barcode', // Optional barcode at variant level
        'name',
        'attributes',
        
        // Pricing (variant-level only)
        'price', // Regular Price
        'sale_price', // Sale Price
        'cost_price',
        
        // Discount fields
        'discount_type',
        'discount_value',
        'discount_active',
        'sale_price_start',
        'sale_price_end',
        
        // Inventory (variant-level only)
        'manage_stock',
        'stock_quantity',
        'stock_status',
        'low_stock_threshold', // Variant-specific low stock threshold
        
        // Dimensions/Measurements (variant-level only)
        'weight',
        'length',
        'width',
        'height',
        'diameter',
        'measurements',
        'highlights_details', // Variant highlights & details
        'description', // Detailed description for variant
        'additional_information', // Additional information for variant
        
        // Variant Images
        'image', // Legacy single image field
        
        // Status
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'sale_price_start' => 'datetime',
        'sale_price_end' => 'datetime',
        'manage_stock' => 'boolean',
        'discount_active' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'low_stock_threshold' => 'integer',
        'measurements' => 'array',
        'highlights_details' => 'array',
    ];

    /**
     * Boot the model and register event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-update stock_status based on total stock quantity
        // This ensures stock_status is always in sync when variant is saved directly
        static::saving(function ($variant) {
            // Only update if manage_stock is enabled and stock_status is being set/changed
            if ($variant->manage_stock && $variant->isDirty(['stock_quantity', 'manage_stock'])) {
                // Calculate total stock from inventory_stocks if variant exists
                $totalStock = 0;
                
                if ($variant->exists) {
                    // Query inventory_stocks to get actual total
                    $totalStock = $variant->inventoryStocks()->sum('quantity');
                    
                    // Fallback to stock_quantity if no warehouse stocks exist
                    if ($totalStock == 0 && ($variant->stock_quantity ?? 0) > 0) {
                        $totalStock = $variant->stock_quantity;
                    }
                } else {
                    // New variant, use stock_quantity
                    $totalStock = $variant->stock_quantity ?? 0;
                }
                
                // Auto-calculate stock status: 0 = out_of_stock, > 0 = in_stock
                $variant->stock_status = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
                
                // Sync stock_quantity if variant exists and we have warehouse stocks
                if ($variant->exists && $totalStock > 0) {
                    $variant->stock_quantity = $totalStock;
                }
            }
        });
    }

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_variant_id')->orderBy('sort_order');
    }


    /**
     * Get inventory stocks for this variant across all warehouses
     */
    public function inventoryStocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    /**
     * Get total stock quantity across all warehouses
     */
    public function getTotalStockQuantityAttribute()
    {
        if (!$this->manage_stock) {
            return null;
        }
        
        // If using warehouse-based inventory, sum from inventory_stocks
        $warehouseStock = $this->inventoryStocks()->sum('quantity');
        
        // Fallback to variant stock_quantity if no warehouse stocks exist
        if ($warehouseStock == 0 && $this->stock_quantity > 0) {
            return $this->stock_quantity;
        }
        
        return $warehouseStock;
    }

    /**
     * Get available stock (total - reserved) across all warehouses
     */
    public function getAvailableStockAttribute()
    {
        if (!$this->manage_stock) {
            return null;
        }
        
        $totalQuantity = $this->inventoryStocks()->sum('quantity');
        $reservedQuantity = $this->inventoryStocks()->sum('reserved_quantity');
        
        // Fallback to variant stock_quantity if no warehouse stocks exist
        if ($totalQuantity == 0 && $this->stock_quantity > 0) {
            return $this->stock_quantity;
        }
        
        return max(0, $totalQuantity - $reservedQuantity);
    }

    /**
     * Get stock for a specific warehouse
     */
    public function getStockForWarehouse($warehouseId, $locationId = null)
    {
        $query = $this->inventoryStocks()->where('warehouse_id', $warehouseId);
        
        if ($locationId) {
            $query->where('warehouse_location_id', $locationId);
        }
        
        return $query->first();
    }

    // Accessors
    public function getCurrentPriceAttribute()
    {
        if ($this->isOnSale()) {
            return $this->sale_price;
        }
        return $this->price ?? 0; // Price is always variant-level
    }

    public function getDiscountPercentageAttribute()
    {
        $basePrice = $this->price ?? 0;
        if ($this->isOnSale() && $basePrice > 0) {
            return round((($basePrice - $this->sale_price) / $basePrice) * 100);
        }
        return 0;
    }

    /**
     * Check if discount is currently active
     */
    public function hasActiveDiscount()
    {
        if (!$this->discount_active) {
            return false;
        }

        if (!$this->discount_type || !$this->discount_value) {
            return false;
        }

        return true;
    }

    /**
     * Calculate final price after discount
     * Discount is applied to sale_price if it exists, otherwise to base price
     */
    public function getFinalPriceAttribute()
    {
        $basePrice = $this->price ?? 0;
        
        // Determine the price to apply discount to (use sale_price if available, otherwise base price)
        $priceToDiscount = $basePrice;
        if ($this->isOnSale() && $this->sale_price) {
            $priceToDiscount = $this->sale_price;
        }

        // Apply discount to the determined price
        if ($this->hasActiveDiscount()) {
            if ($this->discount_type === 'percentage') {
                $discountAmount = ($priceToDiscount * $this->discount_value) / 100;
                return max(0, $priceToDiscount - $discountAmount);
            } elseif ($this->discount_type === 'amount' || $this->discount_type === 'flat') {
                return max(0, $priceToDiscount - $this->discount_value);
            }
        }

        // If no discount, return sale_price if available, otherwise base price
        return $priceToDiscount;
    }

    /**
     * Calculate total savings (base price - final price)
     */
    public function getTotalSavingsAttribute()
    {
        $basePrice = $this->price ?? 0;
        $finalPrice = $this->final_price;
        return max(0, $basePrice - $finalPrice);
    }

    /**
     * Get discount badge text
     * Shows total OFF percentage based on base price vs final price
     * Calculates the actual savings percentage, regardless of how discount was applied
     */
    public function getDiscountBadgeTextAttribute()
    {
        $basePrice = round($this->price ?? 0);
        $finalPrice = round($this->final_price ?? $basePrice);
        
        // Calculate OFF percentage based on actual savings (base_price - final_price)
            if ($finalPrice < $basePrice && $basePrice > 0) {
            $offPercentage = round((($basePrice - $finalPrice) / $basePrice) * 100);
            if ($offPercentage > 0) {
                return $offPercentage . '% OFF';
            }
        }

        return null;
    }

    /**
     * Check if any discount or sale is active
     */
    public function hasDiscountOrSale()
    {
        return $this->isOnSale() || $this->hasActiveDiscount();
    }
    
    /**
     * Check if variant is low on stock
     */
    public function isLowStock()
    {
        if (!$this->manage_stock) {
            return false;
        }
        
        $threshold = $this->low_stock_threshold ?? 0;
        return $this->stock_quantity <= $threshold;
    }

    // Helper methods
    public function isOnSale()
    {
        if (!$this->sale_price) {
            return false;
        }

        $now = now();
        
        if ($this->sale_price_start && $this->sale_price_start > $now) {
            return false;
        }
        
        if ($this->sale_price_end && $this->sale_price_end < $now) {
            return false;
        }
        
        return true;
    }

    public function isInStock()
    {
        if (!$this->manage_stock) {
            return $this->stock_status === 'in_stock';
        }
        
        // Use warehouse inventory if available, otherwise fall back to stock_quantity
        $warehouseStock = $this->inventoryStocks()->sum('quantity');
        if ($warehouseStock > 0) {
            return true;
        }
        
        // Fallback to variant stock_quantity if no warehouse stocks exist
        return $this->stock_quantity > 0;
    }

    public function getAttributeString()
    {
        $attributes = [];
        foreach ($this->attributes as $key => $value) {
            $attributes[] = ucfirst($key) . ': ' . $value;
        }
        return implode(', ', $attributes);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function($q) {
            $q->where('stock_status', 'in_stock')
              ->orWhere(function($q2) {
                  $q2->where('manage_stock', true)
                     ->where('stock_quantity', '>', 0);
              });
        });
    }

    /**
     * Get comprehensive pricing information for this variant
     * This is the centralized pricing method used across all pages (Home, Wishlist, Shop, Cart)
     * 
     * @param bool|null $gstType GST type from product (true = inclusive, false = exclusive, null = default true)
     * @param float $gstPercentage GST percentage from product (default 0)
     * @return array Structured pricing data with all calculated values
     */
    public function getPricingData($gstType = null, $gstPercentage = 0)
    {
        // Get GST settings - use provided values or get from product relationship
        if ($gstType === null && $this->relationLoaded('product')) {
            $gstType = $this->product->gst_type ?? true;
            $gstPercentage = $this->product->gst_percentage ?? 0;
        } elseif ($gstType === null) {
            $gstType = true; // Default to inclusive
        }
        
        // Ensure boolean type
        $gstType = (bool) $gstType;
        
        // Base pricing values - keep in 2 decimals for accurate calculations
        $basePrice = round($this->price ?? 0, 2);
        $salePrice = $this->sale_price ? round($this->sale_price, 2) : null;
        $isOnSale = $this->isOnSale() && $salePrice !== null && $salePrice < $basePrice;
        
        // Determine price to use for discount calculation (sale price if available, otherwise base price)
        $priceToDiscount = $isOnSale ? $salePrice : $basePrice;
        
        // Calculate final price after discount - keep 2 decimals during calculation
        $finalPrice = $priceToDiscount;
        $hasActiveDiscount = $this->hasActiveDiscount();
        
        if ($hasActiveDiscount) {
            if ($this->discount_type === 'percentage') {
                $discountAmount = round(($priceToDiscount * $this->discount_value) / 100, 2);
                $finalPrice = round(max(0, $priceToDiscount - $discountAmount), 2);
            } elseif (in_array($this->discount_type, ['amount', 'flat'])) {
                $finalPrice = round(max(0, $priceToDiscount - $this->discount_value), 2);
            }
        } elseif ($isOnSale) {
            $finalPrice = $salePrice;
        }
        
        // Calculate total savings (base price - final price) - keep 2 decimals
        $totalSavings = round(max(0, $basePrice - $finalPrice), 2);
        
        // Check if product has exclusive tax
        $hasExclusiveTax = (!$gstType && $gstPercentage > 0);
        $hasExtraDiscount = $hasActiveDiscount;
        
        // Initialize display variables - keep 2 decimals during calculations
        $displayBasePrice = $basePrice;
        $displayFinalPrice = $finalPrice;
        $displaySavings = $totalSavings;
        $showBasePrice = false;
        $taxLabel = 'Inclusive of all taxes';
        $gstAmount = 0;
        
        // Apply GST calculations for display - use 2 decimals throughout
        if ($hasExclusiveTax && !$hasExtraDiscount) {
            // Condition One: Exclusive tax without extra discount
            // Calculate tax on sale price (or base price if no sale) - keep 2 decimals
            $gstAmount = round($priceToDiscount * ($gstPercentage / 100), 2);
            $displayFinalPrice = round($priceToDiscount + $gstAmount, 2);
            $showBasePrice = false;
            $taxLabel = 'Inclusive of taxes';
        } elseif ($hasExclusiveTax && $hasExtraDiscount) {
            // Condition Two: Exclusive tax with extra discount
            // Step 1: Calculate tax-inclusive price first - keep 2 decimals
            $gstAmount = round($priceToDiscount * ($gstPercentage / 100), 2);
            $taxInclusivePrice = round($priceToDiscount + $gstAmount, 2);
            
            // Step 2: Apply extra discount on tax-inclusive price - keep 2 decimals
            if ($this->discount_type === 'percentage') {
                $discountAmount = round(($taxInclusivePrice * $this->discount_value) / 100, 2);
                $displayFinalPrice = round(max(0, $taxInclusivePrice - $discountAmount), 2);
            } elseif (in_array($this->discount_type, ['amount', 'flat'])) {
                $displayFinalPrice = round(max(0, $taxInclusivePrice - $this->discount_value), 2);
            } else {
                $displayFinalPrice = $taxInclusivePrice;
            }
            
            // Show tax-inclusive price as base price (strikethrough)
            $displayBasePrice = $taxInclusivePrice;
            $showBasePrice = true;
            
            // Calculate savings from original base price to final price - keep 2 decimals
            $displaySavings = round($basePrice - $displayFinalPrice, 2);
            
            $taxLabel = 'Inclusive of taxes';
        } else {
            // Default: Inclusive tax or no tax
            // Use prices as-is (already in 2 decimals)
            $displayBasePrice = $basePrice;
            $displayFinalPrice = $finalPrice;
            $displaySavings = $totalSavings;
            $showBasePrice = ($displayBasePrice > $displayFinalPrice);
            $taxLabel = $gstType ? 'Inclusive of all taxes' : 'Exclusive of taxes';
        }
        
        // Round display values only at the end for cart total display
        // Keep raw values in 2 decimals for accurate calculations
        $displayBasePriceRounded = round($displayBasePrice);
        $displayFinalPriceRounded = round($displayFinalPrice);
        $displaySavingsRounded = round($displaySavings);
        
        // Calculate OFF badge text based on actual savings (base_price vs display_final_price)
        // Use rounded values for badge calculation
        $discountBadgeText = $this->generateDiscountBadgeText($displayBasePriceRounded, $displayFinalPriceRounded);
        
        // Check if any discount or sale is active
        $hasDiscountOrSale = $isOnSale || $hasActiveDiscount;
        
        return [
            // Raw pricing values (in 2 decimals for accurate calculations)
            'base_price' => $basePrice,
            'sale_price' => $salePrice,
            'final_price' => $finalPrice,
            'is_on_sale' => $isOnSale,
            'has_active_discount' => $hasActiveDiscount,
            'has_discount_or_sale' => $hasDiscountOrSale,
            
            // Display values (after GST calculations if applicable)
            // Raw values in 2 decimals for cart calculations
            'display_base_price' => $displayBasePrice,
            'display_final_price' => $displayFinalPrice,
            'display_savings' => $displaySavings,
            // Rounded values for display only
            'display_base_price_rounded' => $displayBasePriceRounded,
            'display_final_price_rounded' => $displayFinalPriceRounded,
            'display_savings_rounded' => $displaySavingsRounded,
            'show_base_price' => $showBasePrice,
            
            // Discount information
            'discount_badge_text' => $discountBadgeText,
            'total_savings' => $totalSavings,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'discount_active' => $this->discount_active ?? false,
            
            // Tax information
            'gst_type' => $gstType,
            'gst_percentage' => $gstPercentage,
            'gst_amount' => $gstAmount,
            'tax_label' => $taxLabel,
            'has_exclusive_tax' => $hasExclusiveTax,
            
            // Additional metadata
            'price_to_discount' => $priceToDiscount,
        ];
    }

    /**
     * Generate discount badge text for display
     * Calculates total OFF percentage based on base price vs final display price
     * This ensures accurate OFF percentage regardless of sale/discount combinations
     * 
     * @param float $basePrice Original base price
     * @param float $displayFinalPrice Final price to display (after all discounts/taxes)
     * @return string|null OFF badge text or null if no discount
     */
    protected function generateDiscountBadgeText($basePrice, $displayFinalPrice)
    {
        // Calculate OFF percentage based on actual savings
        if ($displayFinalPrice < $basePrice && $basePrice > 0) {
            $offPercentage = round((($basePrice - $displayFinalPrice) / $basePrice) * 100);
            if ($offPercentage > 0) {
                return $offPercentage . '% OFF';
            }
        }
        
        return null;
    }
}