<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('field_management_fields', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('is_active');
        });

        // Mark core fields as system fields
        $coreFields = [
            'full_name',
            'email',
            'phone',
            'password',
            'password_confirmation',
            'address_type',
            'full_address',
            'country',
            'state',
            'city',
            'pincode',
        ];

        foreach ($coreFields as $fieldKey) {
            \DB::table('field_management_fields')
                ->where('field_key', $fieldKey)
                ->update(['is_system' => true, 'is_required' => true, 'is_visible' => true, 'is_active' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('field_management_fields', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
