<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class FixStockStatus extends Command
{
    protected $signature = 'inventory:fix-stock-status {--dry-run : Show what would be changed without actually updating}';
    protected $description = 'Fix incorrect stock_status values based on actual inventory quantities';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('Scanning product variants for incorrect stock_status values...');
        $this->newLine();

        $variants = ProductVariant::where('manage_stock', true)->get();
        $totalVariants = $variants->count();
        $fixed = 0;
        $skipped = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($totalVariants);
        $bar->start();

        foreach ($variants as $variant) {
            try {
                // Calculate total stock from inventory_stocks
                $warehouseStock = $variant->inventoryStocks()->sum('quantity');
                
                // Determine total stock: use warehouse if records exist, otherwise use variant stock_quantity
                if ($variant->inventoryStocks()->count() > 0) {
                    // Has warehouse records - use warehouse total (even if 0)
                    $totalStock = $warehouseStock;
                } else {
                    // No warehouse records - use variant stock_quantity
                    $totalStock = $variant->stock_quantity ?? 0;
                }

                // Determine correct stock status based on actual total stock
                $correctStatus = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
                
                // Check if status needs to be fixed
                if ($variant->stock_status !== $correctStatus) {
                    if (!$dryRun) {
                        // Update stock_status and stock_quantity
                        $variant->stock_quantity = $totalStock;
                        $variant->stock_status = $correctStatus;
                        $variant->saveQuietly(); // Use saveQuietly to avoid triggering events
                    }
                    
                    $fixed++;
                    
                    if ($this->getOutput()->isVerbose()) {
                        $this->newLine();
                        $this->line("Variant ID {$variant->id} ({$variant->sku}): {$variant->stock_status} → {$correctStatus} (Stock: {$totalStock})");
                    }
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors++;
                if ($this->getOutput()->isVerbose()) {
                    $this->newLine();
                    $this->error("Error processing variant ID {$variant->id}: " . $e->getMessage());
                }
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Variants', $totalVariants],
                ['Fixed', $fixed],
                ['Skipped (already correct)', $skipped],
                ['Errors', $errors],
            ]
        );

        if ($dryRun && $fixed > 0) {
            $this->newLine();
            $this->warn("Run without --dry-run to apply {$fixed} fixes.");
        } elseif (!$dryRun && $fixed > 0) {
            $this->newLine();
            $this->info("Successfully fixed {$fixed} product variant(s).");
        } elseif ($fixed == 0) {
            $this->newLine();
            $this->info("All stock statuses are correct. No fixes needed.");
        }

        return 0;
    }
}

