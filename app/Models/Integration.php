<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_type',
        'provider',
        'configuration',
        'status',
    ];

    protected $casts = [
        'configuration' => 'array',
        'status' => 'boolean',
    ];

    /**
     * Get the masked configuration data for display
     * Masks sensitive fields like passwords, secrets, tokens
     */
    public function getMaskedConfiguration(): array
    {
        $config = $this->configuration ?? [];
        $sensitiveFields = [
            'password',
            'key_secret',
            'webhook_secret',
            'api_secret',
            'access_token',
            'webhook_verify_token',
            'admin_api_access_token',
        ];

        foreach ($config as $key => $value) {
            if (in_array($key, $sensitiveFields) && !empty($value)) {
                $config[$key] = '••••••••';
            }
        }

        return $config;
    }

    /**
     * Scope to get active integrations
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope to get by integration type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('integration_type', $type);
    }
}
