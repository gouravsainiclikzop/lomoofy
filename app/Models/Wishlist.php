<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    protected $fillable = [
        'session_id',
        'customer_id',
        'product_id',
    ];

    /**
     * Product relationship
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Customer relationship
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
