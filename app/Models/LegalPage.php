<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    protected $table = 'legal_pages';

    protected $fillable = [
        'terms_conditions',
        'terms_conditions_status',
        'shipping',
        'shipping_status',
        'cancellation_refund',
        'cancellation_refund_status',
        'return_refund_policy',
        'return_refund_policy_status',
        'privacy_policy',
        'privacy_policy_status',
        'disclaimer',
        'disclaimer_status',
    ];

    protected $casts = [
        'terms_conditions_status' => 'boolean',
        'shipping_status' => 'boolean',
        'cancellation_refund_status' => 'boolean',
        'return_refund_policy_status' => 'boolean',
        'privacy_policy_status' => 'boolean',
        'disclaimer_status' => 'boolean',
    ];

    /**
     * Get or create the singleton instance
     */
    public static function getInstance()
    {
        $instance = self::first();
        if (!$instance) {
            $instance = self::create([
                'terms_conditions' => null,
                'terms_conditions_status' => true,
                'shipping' => null,
                'shipping_status' => true,
                'cancellation_refund' => null,
                'cancellation_refund_status' => true,
                'return_refund_policy' => null,
                'return_refund_policy_status' => true,
                'privacy_policy' => null,
                'privacy_policy_status' => true,
                'disclaimer' => null,
                'disclaimer_status' => true,
            ]);
        }
        return $instance;
    }
}
