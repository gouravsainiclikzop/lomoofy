<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if country field already exists
        $countryField = DB::table('field_management_fields')
            ->where('field_key', 'country')
            ->first();

        if (!$countryField) {
            // Insert country field
            DB::table('field_management_fields')->insert([
                'field_key' => 'country',
                'label' => 'Country',
                'input_type' => 'select',
                'placeholder' => 'Select Country',
                'is_required' => true,
                'is_visible' => true,
                'sort_order' => 23,
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
        } else {
            // Update existing country field to ensure it's marked as system
            DB::table('field_management_fields')
                ->where('field_key', 'country')
                ->update([
                    'is_system' => true,
                    'is_active' => true,
                    'is_visible' => true,
                    'is_required' => true,
                    'input_type' => 'select',
                    'updated_at' => now(),
                ]);
        }

        // Also update state and city to be select type if they're still text
        DB::table('field_management_fields')
            ->where('field_key', 'state')
            ->update([
                'input_type' => 'select',
                'placeholder' => 'Select State',
                'updated_at' => now(),
            ]);

        DB::table('field_management_fields')
            ->where('field_key', 'city')
            ->update([
                'input_type' => 'select',
                'placeholder' => 'Select City',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally remove country field (but we'll keep it as it's a system field)
        // DB::table('field_management_fields')->where('field_key', 'country')->delete();
    }
};

