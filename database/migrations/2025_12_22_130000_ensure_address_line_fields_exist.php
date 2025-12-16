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
        // Force insert/update address_line1
        $addressLine1 = DB::table('field_management_fields')
            ->where('field_key', 'address_line1')
            ->first();

        if (!$addressLine1) {
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
        } else {
            // Update to ensure it's visible and active
            DB::table('field_management_fields')
                ->where('field_key', 'address_line1')
                ->update([
                    'label' => 'Address Line 1',
                    'input_type' => 'text',
                    'placeholder' => 'House / Flat / Building / Street',
                    'is_required' => true,
                    'is_visible' => true,
                    'is_active' => true,
                    'is_system' => true,
                    'sort_order' => 21,
                    'field_group' => 'address',
                    'validation_rules' => 'required|max:255',
                    'updated_at' => now(),
                ]);
        }

        // Force insert/update address_line2
        $addressLine2 = DB::table('field_management_fields')
            ->where('field_key', 'address_line2')
            ->first();

        if (!$addressLine2) {
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
            // Update to ensure it's visible and active
            DB::table('field_management_fields')
                ->where('field_key', 'address_line2')
                ->update([
                    'label' => 'Address Line 2',
                    'input_type' => 'text',
                    'placeholder' => 'Area / Locality (optional, not mandatory)',
                    'is_required' => false,
                    'is_visible' => true,
                    'is_active' => true,
                    'is_system' => true,
                    'sort_order' => 22,
                    'field_group' => 'address',
                    'validation_rules' => 'max:255',
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally remove the fields
        DB::table('field_management_fields')
            ->whereIn('field_key', ['address_line1', 'address_line2'])
            ->delete();
    }
};

