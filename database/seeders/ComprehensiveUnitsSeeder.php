<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class ComprehensiveUnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            // Length / Distance Units
            ['name' => 'Millimeter', 'symbol' => 'mm', 'type' => 'length'],
            ['name' => 'Centimeter', 'symbol' => 'cm', 'type' => 'length'],
            ['name' => 'Meter', 'symbol' => 'm', 'type' => 'length'],
            ['name' => 'Kilometer', 'symbol' => 'km', 'type' => 'length'],
            ['name' => 'Inch', 'symbol' => 'in', 'type' => 'length'],
            ['name' => 'Foot', 'symbol' => 'ft', 'type' => 'length'],
            ['name' => 'Yard', 'symbol' => 'yd', 'type' => 'length'],

            // Weight / Mass Units
            ['name' => 'Milligram', 'symbol' => 'mg', 'type' => 'weight'],
            ['name' => 'Gram', 'symbol' => 'g', 'type' => 'weight'],
            ['name' => 'Kilogram', 'symbol' => 'kg', 'type' => 'weight'],
            ['name' => 'Metric Ton', 'symbol' => 't', 'type' => 'weight'],
            ['name' => 'Pound', 'symbol' => 'lb', 'type' => 'weight'],
            ['name' => 'Ounce', 'symbol' => 'oz', 'type' => 'weight'],

            // Volume Units
            ['name' => 'Milliliter', 'symbol' => 'ml', 'type' => 'volume'],
            ['name' => 'Liter', 'symbol' => 'L', 'type' => 'volume'],
            ['name' => 'Gallon', 'symbol' => 'gal', 'type' => 'volume'],

            // Time Units
            ['name' => 'Second', 'symbol' => 's', 'type' => 'time'],
            ['name' => 'Minute', 'symbol' => 'min', 'type' => 'time'],
            ['name' => 'Hour', 'symbol' => 'h', 'type' => 'time'],
            ['name' => 'Day', 'symbol' => 'd', 'type' => 'time'],

            // Temperature Units
            ['name' => 'Celsius', 'symbol' => '°C', 'type' => 'temperature'],
            ['name' => 'Fahrenheit', 'symbol' => '°F', 'type' => 'temperature'],

            // Area Units
            ['name' => 'Square Meter', 'symbol' => 'm²', 'type' => 'area'],
            ['name' => 'Square Foot', 'symbol' => 'ft²', 'type' => 'area'],

            // Other Common Units
            ['name' => 'Piece', 'symbol' => 'pc', 'type' => 'other'],
            ['name' => 'Set', 'symbol' => 'set', 'type' => 'other'],
            ['name' => 'Pack', 'symbol' => 'pack', 'type' => 'other'],
            ['name' => 'Box', 'symbol' => 'box', 'type' => 'other'],
        ];

        foreach ($units as $unitData) {
            Unit::create(array_merge($unitData, ['is_active' => true]));
        } 
    }
}
