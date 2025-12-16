<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update full_address to address_line1
        DB::table('field_management_fields')
            ->where('field_key', 'full_address')
            ->update([
                'field_key' => 'address_line1',
                'label' => 'Address Line 1',
                'placeholder' => 'House / Flat / Building / Street',
                'sort_order' => 21,
                'updated_at' => now(),
            ]);

        // Add address_line2 field
        $addressLine2Exists = DB::table('field_management_fields')
            ->where('field_key', 'address_line2')
            ->exists();

        if (!$addressLine2Exists) {
            DB::table('field_management_fields')->insert([
                'field_key' => 'address_line2',
                'label' => 'Address Line 2',
                'input_type' => 'text',
                'placeholder' => 'Area / Locality (optional, not mandatory)',
                'is_required' => false,
                'is_visible' => true,
                'sort_order' => 22,
                'field_group' => 'address',
                'options' => null,
                'conditional_rules' => null,
                'validation_rules' => 'max:255',
                'help_text' => null,
                'is_active' => true,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Update landmark sort order
        DB::table('field_management_fields')
            ->where('field_key', 'landmark')
            ->update([
                'sort_order' => 23,
                'updated_at' => now(),
            ]);

        // Update pincode label and placeholder
        DB::table('field_management_fields')
            ->where('field_key', 'pincode')
            ->update([
                'label' => 'Pincode / ZIP Code',
                'placeholder' => 'Enter pincode (triggers auto-fill for City & State)',
                'sort_order' => 24,
                'updated_at' => now(),
            ]);

        // Update city label and make it editable note
        DB::table('field_management_fields')
            ->where('field_key', 'city')
            ->update([
                'label' => 'City',
                'placeholder' => 'Auto-filled but editable',
                'sort_order' => 25,
                'updated_at' => now(),
            ]);

        // Update state sort order
        DB::table('field_management_fields')
            ->where('field_key', 'state')
            ->update([
                'sort_order' => 26,
                'updated_at' => now(),
            ]);

        // Update country label and placeholder
        DB::table('field_management_fields')
            ->where('field_key', 'country')
            ->update([
                'label' => 'Country',
                'placeholder' => 'Defaulted, usually locked',
                'sort_order' => 27,
                'updated_at' => now(),
            ]);

        // Update address_type options to include "Work" instead of "office"
        DB::table('field_management_fields')
            ->where('field_key', 'address_type')
            ->update([
                'options' => json_encode([
                    ['value' => 'home', 'label' => 'Home'],
                    ['value' => 'office', 'label' => 'Work'],
                    ['value' => 'other', 'label' => 'Other'],
                ]),
                'sort_order' => 20,
                'updated_at' => now(),
            ]);

        // Update delivery_instructions placeholder
        DB::table('field_management_fields')
            ->where('field_key', 'delivery_instructions')
            ->update([
                'placeholder' => 'Enter delivery instructions (optional)',
                'sort_order' => 28,
                'updated_at' => now(),
            ]);

        // Deactivate or hide make_default_address if it exists (not in user's list)
        DB::table('field_management_fields')
            ->where('field_key', 'make_default_address')
            ->update([
                'is_visible' => false,
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert address_line1 back to full_address
        DB::table('field_management_fields')
            ->where('field_key', 'address_line1')
            ->update([
                'field_key' => 'full_address',
                'label' => 'Full Address',
                'placeholder' => 'Enter complete address',
                'updated_at' => now(),
            ]);

        // Remove address_line2
        DB::table('field_management_fields')
            ->where('field_key', 'address_line2')
            ->delete();

        // Revert other changes
        DB::table('field_management_fields')
            ->where('field_key', 'pincode')
            ->update([
                'label' => 'Pincode',
                'placeholder' => 'Enter pincode',
                'updated_at' => now(),
            ]);

        DB::table('field_management_fields')
            ->where('field_key', 'city')
            ->update([
                'label' => 'City',
                'placeholder' => 'Select City',
                'updated_at' => now(),
            ]);

        DB::table('field_management_fields')
            ->where('field_key', 'country')
            ->update([
                'label' => 'Country',
                'placeholder' => 'Select Country',
                'updated_at' => now(),
            ]);

        DB::table('field_management_fields')
            ->where('field_key', 'address_type')
            ->update([
                'options' => json_encode([
                    ['value' => 'home', 'label' => 'Home'],
                    ['value' => 'office', 'label' => 'Office'],
                    ['value' => 'other', 'label' => 'Other'],
                ]),
                'updated_at' => now(),
            ]);
    }
};

