<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Models\CompanySetting;

class ThemeScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Cache theme type per request to avoid multiple queries
        static $cachedThemeType = null;
        
        if ($cachedThemeType === null) {
            try {
                $settings = CompanySetting::getSettings();
                $cachedThemeType = $settings->theme_type;
            } catch (\Exception $e) {
                $cachedThemeType = null;
            }
        }
        
        if ($cachedThemeType !== null) {
            $builder->where($model->getTable() . '.theme_type', $cachedThemeType);
        }
    }
}
