<?php

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Variant Import Functionality...\n\n";

// First, let's check existing products
echo "=== EXISTING PRODUCTS ===\n";
$products = \App\Models\Product::with('variants')->get();
foreach ($products as $product) {
    echo "Product: {$product->name} (Slug: {$product->slug})\n";
    echo "  Variants: " . $product->variants->count() . "\n";
    foreach ($product->variants as $variant) {
        echo "    - {$variant->name} (SKU: {$variant->sku})\n";
    }
    echo "\n";
}

echo "Total Products: " . $products->count() . "\n";
echo "Total Variants: " . $products->sum(function($p) { return $p->variants->count(); }) . "\n\n";

// Test the variant import
$testCsv = __DIR__ . '/../public/import-templates/variants-only-template.csv';

if (!file_exists($testCsv)) {
    echo "Variant CSV file not found: $testCsv\n";
    exit(1);
}

echo "=== TESTING VARIANT IMPORT ===\n";
echo "File: $testCsv\n";

try {
    $importService = new \App\Services\ProductImportService();
    
    echo "Importing variants...\n";
    $result = $importService->import($testCsv, 'csv', 'test-variants.csv');
    
    echo "Import completed!\n";
    echo "Results:\n";
    echo "- Created: " . $result['created'] . "\n";
    echo "- Updated: " . $result['updated'] . "\n";
    echo "- Skipped: " . $result['skipped'] . "\n";
    echo "- Errors: " . count($result['errors']) . "\n\n";
    
    if (!empty($result['errors'])) {
        echo "Errors:\n";
        foreach ($result['errors'] as $error) {
            echo "- Row {$error['row']}: {$error['message']}\n";
        }
        echo "\n";
    }
    
    // Check products after import
    echo "=== PRODUCTS AFTER VARIANT IMPORT ===\n";
    $products = \App\Models\Product::with('variants')->get();
    foreach ($products as $product) {
        echo "Product: {$product->name} (Slug: {$product->slug})\n";
        echo "  Variants: " . $product->variants->count() . "\n";
        foreach ($product->variants as $variant) {
            echo "    - {$variant->name} (SKU: {$variant->sku})\n";
        }
        echo "\n";
    }
    
    echo "Total Products: " . $products->count() . "\n";
    echo "Total Variants: " . $products->sum(function($p) { return $p->variants->count(); }) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "\nTest completed!\n";
