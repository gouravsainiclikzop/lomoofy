<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class ForceFixStockStatus extends Command
{
    protected $signature = 'inventory:force-fix-stock-status';
    protected $description = 'Force fix stock_status for all variants with 0 stock quantity';

    public function handle()
    {
        $this->info('Force fixing stock status for variants with 0 stock...');
        $this->newLine();

        // Direct database update for variants with 0 stock but in_stock status
        // This handles variants that are NOT in inventory_stocks table (only in product_variants)
        $updated = DB::table('product_variants')
            ->where('manage_stock', true)
            ->where('stock_quantity', 0)
            ->where('stock_status', 'in_stock')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('inventory_stocks')
                      ->whereColumn('inventory_stocks.product_variant_id', 'product_variants.id');
            })
            ->update([
                'stock_status' => 'out_of_stock',
                'updated_at' => now()
            ]);

        $this->info("Updated {$updated} variant(s) (not in inventory_stocks) from 'in_stock' to 'out_of_stock'.");
        $this->newLine();

        // Also update variants that ARE in inventory_stocks but have 0 total stock
        $updated2 = DB::table('product_variants')
            ->where('manage_stock', true)
            ->where('stock_status', 'in_stock')
            ->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM inventory_stocks WHERE inventory_stocks.product_variant_id = product_variants.id) = 0')
            ->where('stock_quantity', 0)
            ->update([
                'stock_status' => 'out_of_stock',
                'updated_at' => now()
            ]);

        $this->info("Updated {$updated2} variant(s) (in inventory_stocks with 0 stock) from 'in_stock' to 'out_of_stock'.");
        $this->newLine();

        // Also update variants where warehouse stock is 0 (using model for consistency)
        $variants = ProductVariant::where('manage_stock', true)->get();
        $fixed = 0;
        
        foreach ($variants as $variant) {
            // Check if variant has inventory_stocks records
            $hasInventoryStocks = $variant->inventoryStocks()->count() > 0;
            
            if ($hasInventoryStocks) {
                // Has warehouse records - use warehouse total
                $totalStock = $variant->inventoryStocks()->sum('quantity');
            } else {
                // No warehouse records - use variant stock_quantity
                $totalStock = $variant->stock_quantity ?? 0;
            }
            
            // If total stock is 0 but status is in_stock, fix it
            if ($totalStock == 0 && $variant->stock_status === 'in_stock') {
                $variant->stock_status = 'out_of_stock';
                $variant->stock_quantity = 0;
                $variant->saveQuietly();
                $fixed++;
            }
        }

        if ($fixed > 0) {
            $this->info("Fixed {$fixed} additional variant(s) through model updates.");
        }
        
        $totalFixed = $updated + $updated2 + $fixed;
        if ($totalFixed > 0) {
            $this->newLine();
            $this->info("Total: {$totalFixed} variant(s) fixed.");
        }

        $this->newLine();
        $this->info('Done! Please refresh your browser (Ctrl+F5) to see the changes.');
        
        return 0;
    }
}

