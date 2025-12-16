<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use SoftDeletes, HasApiTokens;

    protected $fillable = [
        'full_name',
        'phone',
        'alternate_phone',
        'email',
        'date_of_birth',
        'gender',
        'password',
        'profile_image',
        'preferred_contact_method',
        'preferred_payment_method',
        'preferred_delivery_slot',
        'newsletter_opt_in',
        'tags',
        'risk_flags',
        'notes',
        'custom_data',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'newsletter_opt_in' => 'boolean',
        'is_active' => 'boolean',
        'tags' => 'array',
        'risk_flags' => 'array',
        'custom_data' => 'array',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Hash password when setting
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    /**
     * Relationship with addresses
     */
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    /**
     * Get default address
     */
    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddress::class)->where('is_default', true);
    }

    /**
     * Relationship with orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relationship with carts
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Scope to get only active customers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
