<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldManagement extends Model
{
    protected $table = 'field_management_fields';

    protected $fillable = [
        'field_key',
        'label',
        'input_type',
        'placeholder',
        'is_required',
        'is_visible',
        'sort_order',
        'field_group',
        'options',
        'conditional_rules',
        'validation_rules',
        'help_text',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_visible' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'options' => 'array',
        'conditional_rules' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Get core system fields that cannot be edited or deleted
     */
    public static function getSystemFields(): array
    {
        return [
            'full_name',
            'email',
            'phone',
            'password',
            'password_confirmation',
            'address_type',
            'address_line1',
            'address_line2',
            'landmark',
            'country',
            'state',
            'city',
            'pincode',
            'delivery_instructions',
        ];
    }

    /**
     * Get system fields for authentication (login/register)
     */
    public static function getAuthSystemFields(): array
    {
        return [
            'email',
            'password',
            'password_confirmation',
        ];
    }

    /**
     * Get system fields for login only
     */
    public static function getLoginSystemFields(): array
    {
        return [
            'email',
            'password',
        ];
    }

    /**
     * Check if this is a system field
     */
    public function isSystemField(): bool
    {
        return $this->is_system || in_array($this->field_key, self::getSystemFields());
    }

    /**
     * Scope to get only active fields
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only visible fields
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Get fields by group
     */
    public function scopeByGroup($query, $group)
    {
        return $query->where('field_group', $group);
    }
}
