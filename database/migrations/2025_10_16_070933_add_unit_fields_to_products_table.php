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
        Schema::table('products', function (Blueprint $table) {
            // Unit system fields
            $table->foreignId('unit_id')->nullable()->after('height')->constrained('units')->onDelete('set null');
            $table->decimal('unit_quantity', 10, 3)->nullable()->after('unit_id'); // e.g., 1.5 kg, 2.5 L
            $table->string('unit_display')->nullable()->after('unit_quantity'); // e.g., "1.5 kg", "2 pieces"
            
            // Additional product fields for comprehensive e-commerce
            $table->string('color')->nullable()->after('unit_display');
            $table->string('size')->nullable()->after('color');
            $table->string('material')->nullable()->after('size');
            $table->string('origin_country')->nullable()->after('material');
            $table->date('manufacturing_date')->nullable()->after('origin_country');
            $table->date('expiry_date')->nullable()->after('manufacturing_date');
            $table->boolean('is_perishable')->default(false)->after('expiry_date');
            $table->boolean('requires_prescription')->default(false)->after('is_perishable');
            $table->boolean('is_hazardous')->default(false)->after('requires_prescription');
            $table->text('ingredients')->nullable()->after('is_hazardous');
            $table->text('nutritional_info')->nullable()->after('ingredients');
            $table->string('barcode_type')->default('EAN13')->after('nutritional_info'); // EAN13, UPC, etc.
            $table->json('custom_attributes')->nullable()->after('barcode_type'); // For flexible product attributes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn([
                'unit_id', 'unit_quantity', 'unit_display', 'color', 'size', 'material',
                'origin_country', 'manufacturing_date', 'expiry_date', 'is_perishable',
                'requires_prescription', 'is_hazardous', 'ingredients', 'nutritional_info',
                'barcode_type', 'custom_attributes'
            ]);
        });
    }
};