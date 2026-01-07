<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'rating',
        'product_id',
        'user_id',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the product that this review belongs to
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the customer/user who wrote this review
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    /**
     * Scope to get only active reviews
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get only inactive reviews
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope to get reviews for a specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to get reviews by a specific customer
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('user_id', $customerId);
    }
}
