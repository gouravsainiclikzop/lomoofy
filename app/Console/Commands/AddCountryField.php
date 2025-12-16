<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FieldManagement;

class AddCountryField extends Command
{
    protected $signature = 'field:add-country';
    protected $description = 'Add country field to field management if it does not exist';

    public function handle()
    {
        $field = FieldManagement::where('field_key', 'country')->first();

        if ($field) {
            $this->info('Country field already exists.');
            // Update it to ensure it's correct
            $field->update([
                'label' => 'Country',
                'input_type' => 'select',
                'placeholder' => 'Select Country',
                'is_required' => true,
                'is_visible' => true,
                'sort_order' => 23,
                'field_group' => 'address',
                'is_active' => true,
                'is_system' => true,
                'validation_rules' => 'required',
            ]);
            $this->info('Country field updated.');
        } else {
            FieldManagement::create([
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
            ]);
            $this->info('Country field created successfully.');
        }

        // Also update state and city to be select
        $stateField = FieldManagement::where('field_key', 'state')->first();
        if ($stateField) {
            $stateField->update([
                'input_type' => 'select',
                'placeholder' => 'Select State',
            ]);
            $this->info('State field updated to select type.');
        }

        $cityField = FieldManagement::where('field_key', 'city')->first();
        if ($cityField) {
            $cityField->update([
                'input_type' => 'select',
                'placeholder' => 'Select City',
            ]);
            $this->info('City field updated to select type.');
        }

        return 0;
    }
}

