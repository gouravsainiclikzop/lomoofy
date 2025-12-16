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
        // Check if address_line1 exists, if not create it
        $addressLine1Exists = DB::table('field_management_fields')
            ->where('field_key', 'address_line1')
            ->exists();

        if (!$addressLine1Exists) {
            // Check if full_address exists and update it, otherwise create new
            $fullAddressExists = DB::table('field_management_fields')
                ->where('field_key', 'full_address')
                ->exists();

            if ($fullAddressExists) {
                // Update full_address to address_line1
                DB::table('field_management_fields')
                    ->where('field_key', 'full_address')
                    ->update([
                        'field_key' => 'address_line1',
                        'label' => 'Address Line 1',
                        'placeholder' => 'House / Flat / Building / Street',
                        'input_type' => 'text',
                        'sort_order' => 21,
                        'updated_at' => now(),
                    ]);
            } else {
                // Create address_line1 if neither exists
                DB::table('field_management_fields')->insert([
                    'field_key' => 'address_line1',
                    'label' => 'Address Line 1',
                    'input_type' => 'text',
                    'placeholder' => 'House / Flat / Building / Street',
                    'is_required' => true,
                    'is_visible' => true,
                    'sort_order' => 21,
                    'field_group' => 'address',
                    'options' => null,
                    'conditional_rules' => null,
                    'validation_rules' => 'required|max:255',
                    'help_text' => null,
                    'is_active' => true,
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            // Ensure address_line1 has correct properties
            DB::table('field_management_fields')
                ->where('field_key', 'address_line1')
                ->update([
                    'label' => 'Address Line 1',
                    'placeholder' => 'House / Flat / Building / Street',
                    'input_type' => 'text',
                    'is_required' => true,
                    'is_visible' => true,
                    'is_active' => true,
                    'is_system' => true,
                    'sort_order' => 21,
                    'field_group' => 'address',
                    'updated_at' => now(),
                ]);
        }

        // Check if address_line2 exists, if not create it
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
        } else {
            // Ensure address_line2 has correct properties
            DB::table('field_management_fields')
                ->where('field_key', 'address_line2')
                ->update([
                    'label' => 'Address Line 2',
                    'placeholder' => 'Area / Locality (optional, not mandatory)',
                    'input_type' => 'text',
                    'is_required' => false,
                    'is_visible' => true,
                    'is_active' => true,
                    'is_system' => true,
                    'sort_order' => 22,
                    'field_group' => 'address',
                    'updated_at' => now(),
                ]);
        }
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
                'input_type' => 'textarea',
                'updated_at' => now(),
            ]);

        // Remove address_line2
        DB::table('field_management_fields')
            ->where('field_key', 'address_line2')
            ->delete();
    }
};

