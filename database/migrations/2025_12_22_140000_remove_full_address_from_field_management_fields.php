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
        // Remove full_address field from field_management_fields if it still exists
        DB::table('field_management_fields')
            ->where('field_key', 'full_address')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate full_address field if needed (for rollback)
        $fullAddressExists = DB::table('field_management_fields')
            ->where('field_key', 'full_address')
            ->exists();

        if (!$fullAddressExists) {
            DB::table('field_management_fields')->insert([
                'field_key' => 'full_address',
                'label' => 'Full Address',
                'input_type' => 'textarea',
                'placeholder' => 'Enter complete address',
                'is_required' => true,
                'is_visible' => true,
                'sort_order' => 21,
                'field_group' => 'address',
                'options' => null,
                'conditional_rules' => null,
                'validation_rules' => 'required',
                'help_text' => null,
                'is_active' => true,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};

