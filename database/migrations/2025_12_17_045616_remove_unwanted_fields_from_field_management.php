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
        // Fields to remove from field_management_fields table
        $fieldsToRemove = [
            'special_instructions',
            'tags',
            'risk_flags',
            'credit_limit',
            'discount_percentage',
            'loyalty_points',
            'customer_since',
            'last_order_date',
            'total_orders',
            'total_spent',
        ];

        // Delete these fields from the database
        DB::table('field_management_fields')
            ->whereIn('field_key', $fieldsToRemove)
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This migration does not restore the fields
        // If you need to restore them, you would need to run the seeder again
        // or manually insert the field definitions
    }
};
