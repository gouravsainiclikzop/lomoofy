<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Unit;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\SheetInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProductImportService
{
    private const DEFAULT_BRAND_SLUG = 'other';

    private array $allowedProductTypes = [
        'simple',
        'variable',
        'digital',
        'service',
        'bundle',
        'subscription',
    ];

    private array $allowedStatuses = [
        'published',
        'hidden',
    ];

    private array $allowedStockStatuses = [
        'in_stock',
        'out_of_stock',
        'on_backorder',
    ];

    private ?Brand $cachedDefaultBrand = null;

    /**
     * Import products from the provided file.
     *
     * @param  string  $absolutePath
     * @param  string  $extension
     * @param  string|null  $originalName
     * @return array{created:int,updated:int,skipped:int,errors:array<int,array<string,mixed>>,warnings:array<int,string>}
     */
    public function import(string $absolutePath, string $extension, ?string $originalName = null): array
    {
        $result = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'warnings' => [],
        ];

        try {
            $dataset = $this->parseFile($absolutePath, $extension);
        } catch (Throwable $exception) {

            $result['errors'][] = [
                'row' => null,
                'identifier' => $originalName,
                'message' => $exception->getMessage(),
            ];

            return $result;
        }

        $products = $dataset['products'] ?? [];
        // Group variants and images by product identifier (name or slug)
        $variants = $this->groupVariantsByProduct($dataset['variants'] ?? [], $products);
        $images = $this->groupImagesByProduct($dataset['images'] ?? [], $products);
        
       
        foreach ($products as $index => $productRow) {
            $rowNumber = $productRow['_row'] ?? ($index + 2); // header occupies first row

            $name = trim((string) ($productRow['product_name'] ?? $productRow['name'] ?? ''));
            if ($name === '') {
                $result['errors'][] = [
                    'row' => $rowNumber,
                    'identifier' => null,
                    'message' => 'Product Name is required.',
                ];
                $result['skipped']++;
                continue;
            }

            // Use slug if provided, otherwise generate from name
            $slugInput = $productRow['seo_slug'] ?? $productRow['product_slug'] ?? $productRow['slug'] ?? $name;
            $productIdentifier = Str::slug((string) $slugInput);

            try {
                $importOutcome = DB::transaction(function () use ($productIdentifier, $productRow, $variants, $images) {
                    return $this->upsertProduct($productIdentifier, $productRow, $variants[$productIdentifier] ?? [], $images[$productIdentifier] ?? []);
                }, 3);

                if ($importOutcome === 'created') {
                    $result['created']++;
                } elseif ($importOutcome === 'updated') {
                    $result['updated']++;
                } else {
                    $result['skipped']++;
                }
            } catch (Throwable $exception) {
                Log::error('ProductImportService@import - Failed to import product row', [
                    'identifier' => $productIdentifier,
                    'row' => $rowNumber,
                    'message' => $exception->getMessage(),
                ]);

                $result['errors'][] = [
                    'row' => $rowNumber,
                    'identifier' => $productIdentifier,
                    'message' => $exception->getMessage(),
                ];
                $result['skipped']++;
            }
        }

        // Generate product identifiers for all products
        $productIdentifiers = [];
        foreach ($products as $productRow) {
            $name = trim((string) ($productRow['product_name'] ?? $productRow['name'] ?? ''));
            if ($name !== '') {
                $slugInput = $productRow['seo_slug'] ?? $productRow['product_slug'] ?? $productRow['slug'] ?? $name;
                $productIdentifiers[] = Str::slug((string) $slugInput);
            }
        }

        $missingVariantKeys = array_diff(array_keys($variants), $productIdentifiers);
        
        // Log variant processing summary
        
        
        // For variants-only imports, process the "missing" variants by looking up existing products
        if (empty($products) && !empty($missingVariantKeys)) {
           
            
            foreach ($missingVariantKeys as $missingIdentifier) {
                try {
                    $importOutcome = DB::transaction(function () use ($missingIdentifier, $variants, $images) {
                        return $this->upsertProduct($missingIdentifier, [], $variants[$missingIdentifier] ?? [], $images[$missingIdentifier] ?? []);
                    }, 3);

                    if ($importOutcome === 'created') {
                        $result['created']++;
                    } elseif ($importOutcome === 'updated') {
                        $result['updated']++;
                    } else {
                        $result['skipped']++;
                    }
                } catch (Throwable $exception) {
                   

                    $result['errors'][] = [
                        'row' => null,
                        'identifier' => $missingIdentifier,
                        'message' => $exception->getMessage(),
                    ];
                    $result['skipped']++;
                }
            }
        } else {
            // For regular imports, warn about missing variants
            foreach ($missingVariantKeys as $missingIdentifier) {
                $result['warnings'][] = "Variants provided for product identifier '{$missingIdentifier}', but the product sheet does not contain this product. The variants were ignored.";
            }
        }

        $missingImageKeys = array_diff(array_keys($images), $productIdentifiers);
        foreach ($missingImageKeys as $missingIdentifier) {
            $result['warnings'][] = "Images provided for product identifier '{$missingIdentifier}', but the product sheet does not contain this product. The images were ignored.";
        }

        return $result;
    }

    /**
     * Parse the uploaded file into structured arrays.
     *
     * @throws Throwable
     */
    private function parseFile(string $absolutePath, string $extension): array
    {
        $extension = strtolower($extension);

        return match ($extension) {
            'xlsx' => $this->parseSpreadsheet($absolutePath),
            'csv' => $this->parseCsv($absolutePath),
            default => throw new \InvalidArgumentException("Unsupported import file type: {$extension}"),
        };
    }

    /**
     * Parse a spreadsheet (XLSX) file using Spout.
     *
     * @throws IOException
     */
    private function parseSpreadsheet(string $absolutePath): array
    {
        if (!file_exists($absolutePath)) {
            throw new \RuntimeException("XLSX file not found at: {$absolutePath}");
        }

        if (!is_readable($absolutePath)) {
            throw new \RuntimeException("XLSX file is not readable: {$absolutePath}");
        }

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($absolutePath);

        $data = [
            'products' => [],
            'variants' => [],
            'images' => [],
        ];

        foreach ($reader->getSheetIterator() as $sheet) {
            $this->parseSheet($sheet, $data);
        }

        $reader->close();

        return $data;
    }

    /**
     * Parse an individual sheet and append data to the reference arrays.
     */
    private function parseSheet(SheetInterface $sheet, array &$data): void
    {
        $sheetName = strtolower(trim($sheet->getName()));
        $headers = [];
        $rowIndex = 0;

        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex++;
            $cells = $row->toArray();

            // if (empty($headers)) {
            //     $headers = $this->normalizeHeaders($cells);
            //     continue;
            // }

            // Skip comment-only rows before detecting headers
if (!empty($cells[0]) && preg_match('/^\s*#/', trim($cells[0])) !== 0) {
    continue;
}

if (empty($headers)) {
    $headers = $this->normalizeHeaders($cells);
    continue;
}


            $assocRow = $this->mapRow($headers, $cells);
            if ($this->rowIsEmpty($assocRow)) {
                continue;
            }

            $assocRow['_row'] = $rowIndex;

            switch ($sheetName) {
                case 'products':
                case '':
                    $data['products'][] = $assocRow;
                    break;
                case 'variants':
                    $data['variants'][] = $assocRow;
                    break;
                case 'images':
                    $data['images'][] = $assocRow;
                    break;
                default:
                    // Unknown sheet - skip but log once
                    
                    break;
            }
        }
    }

    /**
     * Parse a CSV file into the "products" dataset.
     */
    private function parseCsv(string $absolutePath): array
    {
        if (!file_exists($absolutePath)) {
            throw new \RuntimeException("CSV file not found at: {$absolutePath}");
        }

        if (!is_readable($absolutePath)) {
            throw new \RuntimeException("CSV file is not readable: {$absolutePath}");
        }

        $handle = fopen($absolutePath, 'rb');

        if ($handle === false) {
            throw new \RuntimeException("Unable to open CSV file for reading: {$absolutePath}");
        }

        $headers = [];
        $rows = [];
        $rowIndex = 0;

        while (($cells = fgetcsv($handle)) !== false) {
            $rowIndex++;
            // if ($rowIndex === 1) {
            //     $headers = $this->normalizeHeaders($cells);
            //     continue;
            // }
            // Skip comment-only rows before detecting headers
            if (!empty($cells[0]) && preg_match('/^\s*#/', trim($cells[0])) !== 0) {
                continue;
            }

            if (empty($headers)) {
                $headers = $this->normalizeHeaders($cells);
                continue;
            }


            // Skip comment lines (lines starting with #)
if (!empty($cells[0]) && preg_match('/^\s*#/', trim($cells[0])) !== 0) {
    continue;  // Skip lines that start with # after trimming
}

            $assocRow = $this->mapRow($headers, $cells);
            if ($this->rowIsEmpty($assocRow)) {
                continue;
            }

            $assocRow['_row'] = $rowIndex;
            $rows[] = $assocRow;
        }

        fclose($handle);

        // Determine the type of CSV based on headers
        $csvType = $this->determineCsvType($headers);
        
        return match ($csvType) {
            'variants' => [
                'products' => [],
                'variants' => $rows,
                'images' => [],
            ],
            'images' => [
                'products' => [],
                'variants' => [],
                'images' => $rows,
            ],
            default => [
                'products' => $rows,
                'variants' => [],
                'images' => [],
            ],
        };
    }

    /**
     * Determine the type of CSV based on its headers.
     */
    private function determineCsvType(array $headers): string
    {
        $headerString = strtolower(implode('|', array_filter($headers)));
        
        // Check for variant-specific headers
        $variantHeaders = ['variant_sku', 'variant_name', 'product_name', 'product_slug'];
        $variantHeaderCount = 0;
        foreach ($variantHeaders as $variantHeader) {
            if (str_contains($headerString, $variantHeader)) {
                $variantHeaderCount++;
            }
        }
        
        // Check for image-specific headers
        $imageHeaders = ['image_path', 'image_path_or_url', 'is_primary', 'alt_text'];
        $imageHeaderCount = 0;
        foreach ($imageHeaders as $imageHeader) {
            if (str_contains($headerString, $imageHeader)) {
                $imageHeaderCount++;
            }
        }
        
        // Check for product-specific headers (that are not in variants)
        $productOnlyHeaders = ['short_description', 'long_description', 'status', 'featured', 'tags'];
        $productHeaderCount = 0;
        foreach ($productOnlyHeaders as $productHeader) {
            if (str_contains($headerString, $productHeader)) {
                $productHeaderCount++;
            }
        }
        
        // Determine type based on header analysis
        if ($variantHeaderCount >= 2 && $productHeaderCount === 0) {
            return 'variants';
        } elseif ($imageHeaderCount >= 2 && $variantHeaderCount === 0) {
            return 'images';
        } else {
            return 'products';
        }
    }

    /**
     * Convert the first row into normalized header keys.
     */
    private function normalizeHeaders(array $headerRow): array
    {
        $headers = [];

        foreach ($headerRow as $cell) {
            $value = trim((string) $cell);
            if ($value === '') {
                $headers[] = null;
                continue;
            }

            // Normalize header into snake_case with underscores preserved
            $normalized = Str::slug($value, '_');
            $headers[] = $normalized !== '' ? $normalized : null;
        }

        return $headers;
    }

    /**
     * Map a row of cells to the provided headers.
     */
    private function mapRow(array $headers, array $cells): array
    {
        $mapped = [];

        foreach ($headers as $index => $header) {
            if ($header === null) {
                continue;
            }

            $mapped[$header] = isset($cells[$index]) ? trim((string) $cells[$index]) : null;
        }

        return $mapped;
    }

    /**
     * Determine whether a row is empty (all null or empty strings).
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $key => $value) {
            if ($key === '_row') {
                continue;
            }
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Group rows by a specific key column.
     *
     * @param  array<int,array<string,mixed>>  $rows
     * @return array<string,array<int,array<string,mixed>>>
     */
    private function groupRowsByKey(array $rows, string $key): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $groupKey = trim((string) ($row[$key] ?? ''));
            if ($groupKey === '') {
                continue;
            }

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [];
            }

            $grouped[$groupKey][] = $row;
        }

        return $grouped;
    }

    /**
     * Group variant rows by product identifier (slug only for variants-only imports).
     * Supports both product_sku (legacy) and product_slug.
     *
     * @param  array<int,array<string,mixed>>  $variantRows
     * @param  array<int,array<string,mixed>>  $productRows
     * @return array<string,array<int,array<string,mixed>>>
     */
    private function groupVariantsByProduct(array $variantRows, array $productRows): array
    {
        // Create a mapping from product_sku/name to product identifier
        $productMap = [];
        foreach ($productRows as $productRow) {
            $name = trim((string) ($productRow['product_name'] ?? $productRow['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            
            $slugInput = $productRow['seo_slug'] ?? $productRow['product_slug'] ?? $productRow['slug'] ?? $name;
            $identifier = Str::slug((string) $slugInput);
            
            // Map both legacy SKU and name to identifier (for regular product imports)
            $sku = trim((string) ($productRow['product_sku'] ?? ''));
            if ($sku !== '') {
                $productMap[$sku] = $identifier;
            }
            $productMap[$name] = $identifier;
            $productMap[Str::slug($name)] = $identifier;
        }

        // If we're doing a variants-only import (no products in current batch),
        // we need to check existing products in the database by slug only
        $existingProductMap = [];
        if (empty($productRows) && !empty($variantRows)) {
            $productSlugs = [];
            
            foreach ($variantRows as $row) {
                // For variants-only imports, use only product_slug (not product_name)
                $variantProductSlug = trim((string) ($row['product_slug'] ?? $row['slug'] ?? ''));
                if ($variantProductSlug !== '') {
                    $productSlugs[] = Str::slug($variantProductSlug);
                }
            }
            
            // Find existing products by slug only, including soft-deleted products
            if (!empty($productSlugs)) {
                $existingProducts = Product::withTrashed()
                    ->whereIn('slug', array_unique($productSlugs))
                    ->get();
                
                foreach ($existingProducts as $product) {
                    $existingProductMap[$product->slug] = $product->slug;
                }
            }
        }

        $grouped = [];

        foreach ($variantRows as $row) {
            // Try to find product identifier from variant row
            $productKey = null;
            
            // Try product_sku first (legacy support - only for regular imports)
            $sku = trim((string) ($row['product_sku'] ?? ''));
            if ($sku !== '' && isset($productMap[$sku])) {
                $productKey = $productMap[$sku];
            }
            
            // For variants-only imports, skip product_name and use only product_slug
            // For regular imports, still check product_name if product_slug not found
            if (!$productKey) {
                // Try product_name only if we have product rows (regular import)
                if (!empty($productRows)) {
                    $variantProductName = trim((string) ($row['product_name'] ?? ''));
                    if ($variantProductName !== '' && isset($productMap[$variantProductName])) {
                        $productKey = $productMap[$variantProductName];
                    }
                }
            }
            
            // Try product_slug (primary method for variants-only imports)
            if (!$productKey) {
                $variantProductSlug = trim((string) ($row['product_slug'] ?? $row['slug'] ?? ''));
                if ($variantProductSlug !== '') {
                    $slugCandidate = Str::slug($variantProductSlug);
                    if (isset($existingProductMap[$slugCandidate])) {
                        // Check existing products in database (variants-only import)
                        $productKey = $existingProductMap[$slugCandidate];
                    } elseif (!empty($productRows) && isset($productMap[$slugCandidate])) {
                        // Check product map (regular import)
                        $productKey = $productMap[$slugCandidate];
                    } else {
                        // Use the slug as-is for variants-only imports (product must exist)
                        $productKey = $slugCandidate;
                    }
                }
            }

            if ($productKey === null || $productKey === '') {
                continue;
            }

            if (!isset($grouped[$productKey])) {
                $grouped[$productKey] = [];
            }

            $grouped[$productKey][] = $row;
        }

        return $grouped;
    }

    /**
     * Group image rows by product identifier (name or slug).
     * Supports both product_sku (legacy) and product_name/product_slug (new structure).
     *
     * @param  array<int,array<string,mixed>>  $imageRows
     * @param  array<int,array<string,mixed>>  $productRows
     * @return array<string,array<int,array<string,mixed>>>
     */
    private function groupImagesByProduct(array $imageRows, array $productRows): array
    {
        // Create a mapping from product_sku/name to product identifier
        $productMap = [];
        foreach ($productRows as $productRow) {
            $name = trim((string) ($productRow['product_name'] ?? $productRow['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            
            $slugInput = $productRow['seo_slug'] ?? $productRow['product_slug'] ?? $productRow['slug'] ?? $name;
            $identifier = Str::slug((string) $slugInput);
            
            // Map both legacy SKU and name to identifier
            $sku = trim((string) ($productRow['product_sku'] ?? ''));
            if ($sku !== '') {
                $productMap[$sku] = $identifier;
            }
            $productMap[$name] = $identifier;
            $productMap[Str::slug($name)] = $identifier;
        }

        $grouped = [];

        foreach ($imageRows as $row) {
            // Try to find product identifier from image row
            $productKey = null;
            
            // Try product_sku first (legacy support)
            $sku = trim((string) ($row['product_sku'] ?? ''));
            if ($sku !== '' && isset($productMap[$sku])) {
                $productKey = $productMap[$sku];
            }
            
            // Try product_name
            if (!$productKey) {
                $imageProductName = trim((string) ($row['product_name'] ?? ''));
                if ($imageProductName !== '' && isset($productMap[$imageProductName])) {
                    $productKey = $productMap[$imageProductName];
                } elseif ($imageProductName !== '') {
                    $productKey = Str::slug($imageProductName);
                }
            }
            
            // Try product_slug
            if (!$productKey) {
                $imageProductSlug = trim((string) ($row['product_slug'] ?? $row['slug'] ?? ''));
                if ($imageProductSlug !== '') {
                    $productKey = Str::slug($imageProductSlug);
                }
            }

            if ($productKey === null || $productKey === '') {
                continue;
            }

            if (!isset($grouped[$productKey])) {
                $grouped[$productKey] = [];
            }

            $grouped[$productKey][] = $row;
        }

        return $grouped;
    }

    /**
     * Upsert a product and its related entities.
     *
     * @param  string  $productIdentifier  Product slug/identifier
     * @param  array<string,mixed>  $productRow
     * @param  array<int,array<string,mixed>>  $variantRows
     * @param  array<int,array<string,mixed>>  $imageRows
     */
    private function upsertProduct(string $productIdentifier, array $productRow, array $variantRows, array $imageRows): string
    {
        // Find product by slug, including soft-deleted products
        $product = Product::withTrashed()
            ->with(['brands', 'categories', 'variants', 'images'])
            ->where('slug', $productIdentifier)
            ->first();
        
        $isNew = false;
        $wasDeleted = false;

        if (!$product) {
            $product = new Product();
            $isNew = true;
        } elseif ($product->trashed()) {
            // Restore soft-deleted product
            $product->restore();
            $wasDeleted = true;
        }

        $name = $productRow['product_name'] ?? $productRow['name'] ?? $product->name ?? null;
        if (!$name) {
            // For variants-only imports, if we found an existing product, use its name
            if (!$isNew && $product->name) {
                $name = $product->name;
            } elseif ($isNew && empty($productRow)) {
                // For variants-only imports, product must exist - use slug as fallback name
                // Convert slug back to readable name (capitalize words)
                $name = ucwords(str_replace(['-', '_'], ' ', $productIdentifier));
            } else {
                throw new \RuntimeException('Product Name is required. For variants-only imports, the product must exist in the database.');
            }
        }

        $status = strtolower((string) ($productRow['status'] ?? $product->status ?? 'hidden'));
        if (!in_array($status, $this->allowedStatuses, true)) {
            $status = 'hidden';
        }

        // Note: Price and sale_price are now variant-level only, removed from product
        // Note: 'type' field was removed from products table - all products support variants
        // For variants-only imports, use the productIdentifier (slug) directly
        $slugInput = $productRow['seo_slug'] ?? $productRow['product_slug'] ?? $productRow['slug'] ?? null;
        if ($slugInput === null && empty($productRow)) {
            // Variants-only import: use the identifier passed to this method
            $slugCandidate = $productIdentifier;
        } else {
            $slugCandidate = Str::slug((string) ($slugInput ?? $name));
        }
        $slug = $this->generateUniqueSlug($slugCandidate, $product->id);

        $product->fill([
            'name' => $name,
            'slug' => $slug,
            'description' => $productRow['description'] ?? $product->description,
            'short_description' => $productRow['short_description'] ?? $product->short_description,
            'status' => $status,
            'featured' => $this->toBoolean($productRow['featured'] ?? $productRow['is_featured'] ?? null, false),
            'tags' => $this->normalizeTags($productRow['tag_list'] ?? $productRow['tags'] ?? null),
            'gst_type' => $this->toBoolean($productRow['gst_type'] ?? null, true),
            'gst_percentage' => $this->toDecimal($productRow['gst_percentage'] ?? null) ?? $product->gst_percentage ?? null,
            'requires_shipping' => $this->toBoolean($productRow['requires_shipping'] ?? null, true),
            'free_shipping' => $this->toBoolean($productRow['free_shipping'] ?? null, false),
            'meta_title' => $productRow['meta_title'] ?? $product->meta_title ?? null,
            'meta_description' => $productRow['meta_description'] ?? $product->meta_description ?? null,
            'meta_keywords' => $productRow['meta_keywords'] ?? $product->meta_keywords ?? null,
        ]);

        $product->save();

        // Brand and Category are product-level attributes, not variant-level
        // They should be set during product import, not variant import
        $brandSlugs = $productRow['brand_slugs_(comma_separated)'] 
            ?? $productRow['brand_slugs_comma_separated'] 
            ?? $productRow['brand_slugs'] 
            ?? null;
            
        $categorySlugFromProduct = $productRow['category_slug'] 
            ?? $productRow['category_slugs_(comma_separated)'] 
            ?? $productRow['category_slugs_comma_separated'] 
            ?? $productRow['category_slugs'] 
            ?? $productRow['subcategory_slugs_(comma_separated)'] 
            ?? $productRow['subcategory_slugs_comma_separated'] 
            ?? $productRow['subcategory_slugs'] 
            ?? null;

        $brandIds = $this->resolveBrandIds($brandSlugs);
        $this->syncProductBrands($product, $brandIds);

        // Resolve category (supports unified hierarchy - use deepest category if multiple provided)
        // Support both legacy column names for backward compatibility
        $categorySlug = $categorySlugFromProduct;
        
        $categoryIds = $this->resolveCategoryIds($categorySlug);
        
        // Use the deepest category (last in list) or first if only one
        $categoryId = !empty($categoryIds) ? end($categoryIds) : null;
        $product->category_id = $categoryId;
        $product->save();

        $this->syncVariants($product, $variantRows);

        $this->syncImages($product, $imageRows);

        if ($isNew) {
            return 'created';
        } elseif ($wasDeleted) {
            return 'updated'; // Restored from soft-delete
        } else {
            return 'updated';
        }
    }

    /**
     * Normalize tags string.
     */
    private function normalizeTags(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $parts = array_filter(array_map(static fn ($tag) => trim((string) $tag), explode(',', $value)));

        return empty($parts) ? null : implode(', ', $parts);
    }

    /**
     * Convert numeric input to decimal.
     */
    private function toDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $sanitized = str_replace([',', '₹', '$'], '', (string) $value);
        return is_numeric($sanitized) ? round((float) $sanitized, 2) : null;
    }

    /**
     * Parse user-friendly attributes format into JSON
     * New Format: color:blue:#0000FF|size:S|material:cotton
     * - Color format: color:label:code or color:label (code optional)
     * - Variable format: attribute_name:value
     * 
     * Output format:
     * {
     *   "color": {"label": "blue", "code": "#0000FF"},
     *   "variable": {"size": "S", "material": "cotton"}
     * }
     */
    private function parseUserFriendlyAttributes(?string $attributesString): array
    {
        if (empty($attributesString)) {
            return [];
        }
        
        $result = [
            'variable' => []
        ];
        $hasColor = false;
        $colorData = null;
        
        $pairs = explode('|', $attributesString);
        
        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if (empty($pair)) continue;
            
            if (strpos($pair, ':') !== false) {
                $parts = explode(':', $pair);
                $key = trim($parts[0]);
                
                if (empty($key)) continue;
                
                // Handle color attribute (special case)
                if (strtolower($key) === 'color') {
                    $label = isset($parts[1]) ? trim($parts[1]) : '';
                    $code = isset($parts[2]) ? trim($parts[2]) : '';
                    
                    if (!empty($label)) {
                        // If code not provided, try to get it from color name or use default
                        if (empty($code)) {
                            $code = $this->getColorCodeFromName($label);
                        }
                        
                        // Validate hex color code
                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $code)) {
                            $code = $this->getColorCodeFromName($label);
                        }
                        
                        $hasColor = true;
                        $colorData = [
                            'label' => $label,
                            'code' => $code
                        ];
                    }
                } else {
                    // Handle variable attributes
                    $value = isset($parts[1]) ? trim($parts[1]) : '';
                    if (!empty($value)) {
                        $result['variable'][$key] = $value;
                    }
                }
            }
        }
        
        // Add color if present
        if ($hasColor && $colorData) {
            $result['color'] = $colorData;
        }
        
        // If no variable attributes, remove empty variable key
        if (empty($result['variable'])) {
            unset($result['variable']);
        }
        
        return $result;
    }
    
    /**
     * Parse user-friendly measurements format into JSON
     * Format: attribute_name:value:unit_symbol|attribute_name:value:unit_symbol
     * Example: chest size:26:in|collar:10:in
     * 
     * Output format:
     * [
     *   {
     *     "attribute_id": null,
     *     "attribute_name": "chest size",
     *     "attribute_slug": null,
     *     "value": 26,
     *     "unit_id": 9,
     *     "unit_name": "Inches",
     *     "unit_symbol": "in",
     *     "unit_type": "length"
     *   }
     * ]
     */
    private function parseUserFriendlyMeasurements(?string $measurementsString): array
    {
        if (empty($measurementsString)) {
            return [];
        }
        
        $result = [];
        $measurements = explode('|', $measurementsString);
        
        foreach ($measurements as $measurement) {
            $measurement = trim($measurement);
            if (empty($measurement)) continue;
            
            // Split by colon: attribute_name:value:unit_symbol
            $parts = explode(':', $measurement);
            if (count($parts) < 3) {
                continue; // Skip invalid format
            }
            
            $attributeName = trim($parts[0]);
            $value = trim($parts[1]);
            $unitSymbol = trim($parts[2]);
            
            if (empty($attributeName) || empty($value) || empty($unitSymbol)) {
                continue;
            }
            
            // Validate value is numeric
            if (!is_numeric($value)) {
                Log::warning('ProductImportService - Invalid measurement value', [
                    'measurement' => $measurement,
                    'value' => $value
                ]);
                continue;
            }
            
            // Look up unit by symbol
            $unit = Unit::where('symbol', $unitSymbol)->first();
            
            if (!$unit) {
                Log::warning('ProductImportService - Unit not found by symbol', [
                    'unit_symbol' => $unitSymbol,
                    'measurement' => $measurement
                ]);
                continue;
            }
            
            $result[] = [
                'attribute_id' => null,
                'attribute_name' => $attributeName,
                'attribute_slug' => null,
                'value' => (float) $value,
                'unit_id' => $unit->id,
                'unit_name' => $unit->name,
                'unit_symbol' => $unit->symbol,
                'unit_type' => $unit->type,
            ];
        }
        
        return $result;
    }
    
    /**
     * Get color code from color name (fallback when code not provided).
     */
    private function getColorCodeFromName(string $colorName): string
    {
        $colorMap = [
            'black' => '#000000',
            'white' => '#FFFFFF',
            'red' => '#FF0000',
            'green' => '#008000',
            'blue' => '#0000FF',
            'yellow' => '#FFFF00',
            'orange' => '#FFA500',
            'purple' => '#800080',
            'pink' => '#FFC0CB',
            'brown' => '#A52A2A',
            'grey' => '#808080',
            'gray' => '#808080',
            'silver' => '#C0C0C0',
            'gold' => '#FFD700',
            'navy' => '#000080',
            'maroon' => '#800000',
            'olive' => '#808000',
            'lime' => '#00FF00',
            'aqua' => '#00FFFF',
            'teal' => '#008080',
            'fuchsia' => '#FF00FF',
        ];
        
        $normalized = strtolower(trim($colorName));
        return $colorMap[$normalized] ?? '#000000'; // Default to black if not found
    }

    /**
     * Parse user-friendly highlights format into JSON
     * Format: Heading1>>point1|point2|point3##Heading2>>point1|point2
     */
    private function parseUserFriendlyHighlights(?string $highlightsString): array
    {
        if (empty($highlightsString)) {
            return [];
        }
        
        $highlights = [];
        $sections = explode('##', $highlightsString);
        
        foreach ($sections as $section) {
            $section = trim($section);
            if (empty($section)) continue;
            
            if (strpos($section, '>>') !== false) {
                [$heading, $pointsString] = explode('>>', $section, 2);
                $heading = trim($heading);
                $pointsString = trim($pointsString);
                
                if (!empty($heading) && !empty($pointsString)) {
                    $points = array_filter(array_map('trim', explode('|', $pointsString)));
                    
                    if (!empty($points)) {
                        $highlights[] = [
                            'heading_name' => $heading,
                            'bullet_points' => array_values($points)
                        ];
                    }
                }
            }
        }
        
        return $highlights;
    }

    /**
     * Generate a unique slug for the product.
     */
    private function generateUniqueSlug(string $slugBase, ?int $ignoreId = null): string
    {
        $slug = $slugBase !== '' ? $slugBase : Str::random(8);
        $original = $slug;
        $counter = 1;

        while (Product::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$original}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Resolve brand IDs from slug string.
     *
     * @return array<int,int>
     */
    private function resolveBrandIds(?string $slugList): array
    {
        $slugs = $this->splitList($slugList);
        if (empty($slugs)) {
            return [];
        }

        $brands = Brand::whereIn('slug', $slugs)->get()->keyBy('slug');

        $ids = [];
        foreach ($slugs as $slug) {
            if (isset($brands[$slug])) {
                $ids[] = $brands[$slug]->id;
            }
        }

        return $ids;
    }

    /**
     * Resolve category IDs from slug string.
     *
     * @return array<int,int>
     */
    private function resolveCategoryIds(?string $slugList): array
    {
        $slugs = $this->splitList($slugList);
        if (empty($slugs)) {
            return [];
        }

        $categories = Category::whereIn('slug', $slugs)->get()->keyBy('slug');

        $ids = [];
        foreach ($slugs as $slug) {
            if (isset($categories[$slug])) {
                $ids[] = $categories[$slug]->id;
            }
        }

        return $ids;
    }

    /**
     * Split a delimited list into array of slugs.
     *
     * @return array<int,string>
     */
    private function splitList(?string $list): array
    {
        if ($list === null || trim($list) === '') {
            return [];
        }

        // Support comma, pipe, and space delimiters
        $normalized = str_replace(['|', ' '], ',', $list);
        $parts = array_filter(array_map(static fn ($item) => Str::slug(trim((string) $item)), explode(',', $normalized)));

        return array_values(array_unique(array_filter($parts)));
    }

    /**
     * Sync product-brand relationship, ensuring an "Other" brand exists.
     */
    private function syncProductBrands(Product $product, array $brandIds): void
    {
        if (empty($brandIds)) {
            $defaultBrand = $this->getDefaultBrand();
            $brandIds = [$defaultBrand->id];
        }

        // Brand is now handled via brand_id column, no pivot table needed
        if (!empty($brandIds)) {
            $product->brand_id = $brandIds[0];
            $product->save();
        }
    }


    /**
     * Sync product variants according to the import rows.
     *
     * @param  array<int,array<string,mixed>>  $variantRows
     */
    private function syncVariants(Product $product, array $variantRows): void
    {
        if (empty($variantRows)) {
            return;
        } 

        $existingVariants = $product->variants()->get()->keyBy('sku');
        $processedSkus = [];
        $sortOrder = 0;

        foreach ($variantRows as $row) {
            $variantSku = trim((string) ($row['variant_sku'] ?? $row['sku'] ?? ''));
            if ($variantSku === '') {
                // Generate SKU from product slug and variant attributes or index
                $variantSku = $product->slug . '-V' . ($sortOrder + 1);
            }

            $processedSkus[] = $variantSku;
            
            
            // First, check if variant exists for this product
            $variant = $existingVariants->get($variantSku);
            $isNew = false;

            if (!$variant) {
                // Check if there's an orphaned variant with this SKU globally
                $orphanedVariant = ProductVariant::where('sku', $variantSku)
                    ->whereDoesntHave('product')
                    ->first();
                
                if ($orphanedVariant) {
                    // Reassign the orphaned variant to this product
                    $variant = $orphanedVariant;
                    $variant->product_id = $product->id;
                } else {
                    // Check if there's a variant with this SKU belonging to a different product
                    $existingVariant = ProductVariant::where('sku', $variantSku)->first();
                    
                    if ($existingVariant) {
                        // Generate a unique SKU to avoid conflict
                        $counter = 1;
                        $originalSku = $variantSku;
                        do {
                            $variantSku = $originalSku . '-' . $counter;
                            $counter++;
                        } while (ProductVariant::where('sku', $variantSku)->exists());
                        
                        // Update the processed SKUs array
                        $processedSkus[array_search($originalSku, $processedSkus)] = $variantSku;
                    }
                    
                    // Create new variant
                    $variant = new ProductVariant();
                    $isNew = true;
                }
            }

            $variantName = $row['variant_name'] ?? ($variant->name ?? null);
            if (!$variantName) {
                $variantName = $this->generateVariantNameFromAttributes($row['attributes_json'] ?? null, $variantSku);
            }

            // Parse user-friendly attributes format first, fallback to JSON
            // Try to find attributes column (header might be normalized differently)
            // The CSV header "Attributes (color:label:code|variable:value format)" 
            // gets normalized to something like "attributes_color_label_code_variable_value_format"
            $attributesString = null;
            
            // Search for any key containing "attributes" (case-insensitive)
            // This handles various header formats
            foreach ($row as $key => $value) {
                // Check if key contains "attributes" (case-insensitive) and value is not empty
                if (stripos($key, 'attributes') !== false && 
                    $value !== null && 
                    trim((string)$value) !== '' &&
                    stripos($key, 'json') === false) { // Exclude attributes_json
                    $attributesString = trim((string)$value);
                    break;
                }
            }
            
            // Fallback to specific known keys if not found
            if (!$attributesString || $attributesString === '') {
                $attributesString = $row['attributes'] ?? 
                                   $row['attributes_keyvalue_format'] ?? 
                                   $row['attributes_color_label_code_variable_value_format'] ??
                                   null;
                if ($attributesString) {
                    $attributesString = trim((string)$attributesString);
                }
            }
            
            // Parse attributes
            $attributes = [];
            if ($attributesString && $attributesString !== '') {
                $attributes = $this->parseUserFriendlyAttributes($attributesString);
            }
            
            // Fallback to JSON if parsing returned empty
            if (empty($attributes)) {
                $jsonAttributes = $this->decodeJsonColumn($row['attributes_json'] ?? null);
                if (!empty($jsonAttributes)) {
                    $attributes = $jsonAttributes;
                }
            }
            
            // If still empty and variant exists, preserve existing attributes (don't overwrite with empty)
            // Only set empty if this is a new variant or attributes were explicitly provided and parsed successfully
            if (empty($attributes) && $variant && $variant->exists) {
                if (!empty($attributesString)) {
                    // Attributes string was provided but parsing failed - log warning but preserve existing
                } else {
                    // No attributes string found - preserve existing attributes when updating
                }
                // Keep existing attributes instead of overwriting with empty
                $attributes = $variant->attributes ?? [];
            } elseif (empty($attributes) && (!$variant || !$variant->exists)) {
                // New variant with no attributes - set to empty array
                $attributes = [];
            }
            
            // Log warning if attributes are empty but we had a string
            if (empty($attributes) && !empty($attributesString)) {
            }
            
            // Parse measurements from CSV format: attribute_name:value:unit_symbol|attribute_name:value:unit_symbol
            $measurementsString = null;
            
            // Search for any key containing "measurements" (case-insensitive)
            foreach ($row as $key => $value) {
                if (stripos($key, 'measurements') !== false && 
                    $value !== null && 
                    trim((string)$value) !== '' &&
                    stripos($key, 'json') === false) { // Exclude measurements_json
                    $measurementsString = trim((string)$value);
                    break;
                }
            }
            
            // Fallback to specific known keys
            if (!$measurementsString || $measurementsString === '') {
                $measurementsString = $row['measurements'] ?? null;
                if ($measurementsString) {
                    $measurementsString = trim((string)$measurementsString);
                }
            }
            
            $measurements = [];
            if ($measurementsString && $measurementsString !== '') {
                $measurements = $this->parseUserFriendlyMeasurements($measurementsString);
            }
            
            // Fallback to JSON if parsing returned empty
            if (empty($measurements)) {
                $jsonMeasurements = $this->decodeJsonColumn($row['measurements_json'] ?? null);
                if (!empty($jsonMeasurements)) {
                    $measurements = $jsonMeasurements;
                }
            }
            
            // If still empty and variant exists, preserve existing measurements
            if (empty($measurements) && $variant && $variant->exists) {
                if (!empty($measurementsString)) {
                }
                $measurements = $variant->measurements ?? [];
            }

            $variant->fill([
                'product_id' => $product->id,
                'sku' => $variantSku,
                'barcode' => isset($row['barcode']) && trim((string) $row['barcode']) !== '' 
                    ? trim((string) $row['barcode']) 
                    : ($variant->barcode ?? null),
                'name' => $variantName,
                'price' => $this->toDecimal($row['price'] ?? null) ?? 0,
                'sale_price' => $this->toDecimal($row['sale_price'] ?? null),
                'cost_price' => $this->toDecimal($row['cost_price'] ?? null),
                'stock_quantity' => (int) ($row['stock_quantity'] ?? $variant->stock_quantity ?? 0),
                'manage_stock' => $this->toBoolean($row['manage_stock_(0_or_1)'] ?? $row['manage_stock'] ?? null, true),
                'stock_status' => $this->normalizeStockStatus($row['stock_status'] ?? $variant->stock_status ?? 'in_stock'),
                'low_stock_threshold' => isset($row['low_stock_threshold']) && trim((string) $row['low_stock_threshold']) !== ''
                    ? (int) trim((string) $row['low_stock_threshold'])
                    : ($variant->low_stock_threshold ?? null),
                'is_active' => $this->toBoolean($row['is_active_(0_or_1)'] ?? $row['is_active'] ?? null, true),
                'discount_type' => $this->normalizeDiscountType($row['discount_type'] ?? null),
                'discount_value' => $this->toDecimal($row['discount_value'] ?? null),
                'discount_active' => $this->toBoolean($row['discount_active'] ?? null, false),
                'attributes' => $attributes,
                'measurements' => $measurements,
                'weight' => $this->toDecimal($row['weight'] ?? null),
                'length' => $this->toDecimal($row['length'] ?? null),
                'width' => $this->toDecimal($row['width'] ?? null),
                'height' => $this->toDecimal($row['height'] ?? null),
                'diameter' => $this->toDecimal($row['diameter'] ?? null),
                'sort_order' => $sortOrder,
                'highlights_details' => $this->parseUserFriendlyHighlights(
                    $row['highlights_details_structured_format'] ?? 
                    $row['highlights_details'] ?? 
                    null
                ) ?: $this->decodeJsonColumn($row['highlights_details_json'] ?? null),
                'description' => $row['detailed_description'] ?? $row['description'] ?? $variant->description ?? null,
            ]);

            
            
            $variant->save();
            
            // Reload to verify attributes were saved
            $variant->refresh();
            
            // Handle variant images if provided
            // Look for primary image and additional images columns
            $primaryImagePath = null;
            $additionalImagesString = null;
            
            // Debug: Log all available keys to help identify column name issues
            $imageRelatedKeys = array_filter(array_keys($row), function($key) {
                return stripos($key, 'image') !== false;
            });
            if (!empty($imageRelatedKeys)) {
                
            }
            
            // Helper function for case-insensitive key lookup
            $getValueCaseInsensitive = function($row, $possibleKeys) {
                foreach ($possibleKeys as $key) {
                    // Direct match
                    if (isset($row[$key])) {
                        return $row[$key];
                    }
                    // Case-insensitive match
                    foreach (array_keys($row) as $rowKey) {
                        if (strtolower($rowKey) === strtolower($key)) {
                            return $row[$rowKey];
                        }
                    }
                }
                return null;
            };
            
            // First, try direct key lookup (most reliable)
            // "Primary Image Path" normalizes to "primary_image_path"
            // "Additional Image Paths (comma-separated)" normalizes to "additional_image_paths_comma_separated"
            
            // Try all possible normalized variations for primary image
            $primaryImagePath = $getValueCaseInsensitive($row, [
                'primary_image_path',
                'primary_image',
                'primaryimagepath',
                'primary_imagepath',
                'primaryimage_path'
            ]);
            if ($primaryImagePath) {
                $primaryImagePath = trim((string)$primaryImagePath);
                if ($primaryImagePath === '') {
                    $primaryImagePath = null;
                } else {
                    
                }
            }
            
            // Try all possible normalized variations for additional images
            $additionalImagesString = $getValueCaseInsensitive($row, [
                'additional_image_paths_comma_separated',
                'additional_images',
                'additional_image_paths',
                'additionalimagepathscomma_separated',
                'additionalimagepaths',
                'additional_imagepaths_comma_separated'
            ]);
            if ($additionalImagesString) {
                $additionalImagesString = trim((string)$additionalImagesString);
                if ($additionalImagesString === '') {
                    $additionalImagesString = null;
                } else {
                    
                }
            }
            
            // If not found via direct lookup, search for primary image column by pattern
            if (!$primaryImagePath) {
                foreach ($row as $key => $value) {
                    if (stripos($key, 'primary') !== false && 
                        stripos($key, 'image') !== false && 
                        $value !== null) {
                        $trimmed = trim((string)$value);
                        if ($trimmed !== '') {
                            $primaryImagePath = $trimmed;
                            Log::info('Found primary image column by pattern search', [
                                'column_key' => $key,
                                'value' => $primaryImagePath,
                                'variant_sku' => $variantSku,
                            ]);
                            break;
                        }
                    }
                }
            }
            
            // If not found via direct lookup, search for additional images column by pattern
            if (!$additionalImagesString) {
                foreach ($row as $key => $value) {
                    if ((stripos($key, 'additional') !== false || stripos($key, 'extra') !== false) && 
                        stripos($key, 'image') !== false && 
                        $value !== null) {
                        $trimmed = trim((string)$value);
                        if ($trimmed !== '') {
                            $additionalImagesString = $trimmed;
                            Log::info('Found additional images column by pattern search', [
                                'column_key' => $key,
                                'value' => $additionalImagesString,
                                'variant_sku' => $variantSku,
                            ]);
                            break;
                        }
                    }
                }
            }
            
            // Debug: Log what we found (or didn't find) in image columns
            $primaryImageValue = $getValueCaseInsensitive($row, [
                'primary_image_path',
                'primary_image'
            ]);
            $additionalImagesValue = $getValueCaseInsensitive($row, [
                'additional_image_paths_comma_separated',
                'additional_images',
                'additional_image_paths'
            ]);
            
            $primaryImageTrimmed = $primaryImageValue ? trim((string)$primaryImageValue) : null;
            $additionalImagesTrimmed = $additionalImagesValue ? trim((string)$additionalImagesValue) : null;
            
           
            
            // Combine primary and additional images
            $allImagePaths = [];
            if ($primaryImagePath && $primaryImagePath !== '') {
                $allImagePaths[] = trim($primaryImagePath);
            }
            if ($additionalImagesString && $additionalImagesString !== '') {
                $additionalPaths = array_map('trim', explode(',', $additionalImagesString));
                $additionalPaths = array_filter($additionalPaths, function($path) {
                    return !empty($path);
                });
                $allImagePaths = array_merge($allImagePaths, $additionalPaths);
            }
            
            // Remove duplicates while preserving order (first occurrence wins)
            $uniqueImagePaths = [];
            $seen = [];
            foreach ($allImagePaths as $path) {
                $normalizedPath = trim(strtolower($path));
                if (!isset($seen[$normalizedPath])) {
                    $seen[$normalizedPath] = true;
                    $uniqueImagePaths[] = $path;
                }
            }
            
            if (!empty($uniqueImagePaths)) {
                if (count($uniqueImagePaths) !== count($allImagePaths)) {
                    Log::debug('Removed duplicate image URLs', [
                        'variant_sku' => $variantSku,
                        'original_count' => count($allImagePaths),
                        'unique_count' => count($uniqueImagePaths),
                    ]);
                }
                Log::info('Processing variant images', [
                    'variant_sku' => $variantSku,
                    'variant_id' => $variant->id,
                    'product_id' => $product->id,
                    'image_count' => count($uniqueImagePaths),
                ]);
                $this->syncVariantImages($product, $variant, implode(',', $uniqueImagePaths));
            } else {
                Log::debug('No variant images found in row', [
                    'variant_sku' => $variantSku,
                    'available_keys' => array_keys($row),
                ]);
            }
            
            $sortOrder++;

            if ($isNew) {
                $existingVariants->put($variantSku, $variant);
            }
        }

        $variantSkusToDelete = $existingVariants->keys()->diff($processedSkus);
        if ($variantSkusToDelete->isNotEmpty()) {
            $product->variants()->whereIn('sku', $variantSkusToDelete->all())->each(function (ProductVariant $variant) {
                $variant->images()->delete();
                $variant->delete();
            });
        }
    }
 
    /**
     * Cache for resolved image paths to avoid duplicate downloads
     * Key: normalized URL, Value: resolved path or false if failed
     */
    private static $imageResolutionCache = [];
    
    /**
     * Set of URLs that have failed to resolve (to avoid retrying)
     */
    private static $failedUrls = [];

    private function syncVariantImages(Product $product, ProductVariant $variant, string $imagesString): void
    {
        if (empty($imagesString) || trim($imagesString) === '') {
            return;
        }
    
        // Split comma-separated image paths
        $imagePaths = array_map('trim', explode(',', $imagesString));
        $imagePaths = array_filter($imagePaths, function($path) {
            return !empty($path);
        });
    
        if (empty($imagePaths)) {
            return;
        }
    
        // Don't remove duplicates - allow same URL to appear multiple times
        // (user may want multiple entries even if URLs are the same)
        $imagePaths = array_values($imagePaths);
    
        $storedPaths = [];
        $failedCount = 0;
        $skippedCount = 0;
    
        foreach ($imagePaths as $imagePath) {
            // Normalize URL for cache key (for caching download results)
            $cacheKey = strtolower(trim($imagePath));
            
            // Check if we've already tried this URL and it failed
            if (isset(self::$failedUrls[$cacheKey])) {
                $skippedCount++;
                continue;
            }
            
            // Check cache first - use cached resolved path if available
            if (isset(self::$imageResolutionCache[$cacheKey])) {
                $resolvedPath = self::$imageResolutionCache[$cacheKey];
                if ($resolvedPath !== false && $resolvedPath !== null) {
                    // Add resolved path to stored paths (even if duplicate resolved path)
                    // This allows multiple entries of same URL to create multiple image records
                    $storedPaths[] = $resolvedPath;
                    continue;
                } else {
                    // Previously failed, skip
                    $failedCount++;
                    continue;
                }
            }
            
            // resolveImagePath will handle Google Drive URL normalization
            $resolvedPath = $this->resolveImagePath($imagePath);
            
            // Cache the result (so we don't download same URL again)
            self::$imageResolutionCache[$cacheKey] = $resolvedPath;
    
            if (!$resolvedPath) {
                // Mark as failed
                self::$failedUrls[$cacheKey] = true;
                $failedCount++;
                
                // Only log first failure per unique URL
                $isGoogleDrive = stripos($imagePath, 'drive.google.com') !== false;
                if ($isGoogleDrive) {
                    $fileId = $this->extractGoogleDriveFileId($imagePath);
                    Log::warning('Variant image import failed', [
                        'variant_id' => $variant->id,
                        'product_id' => $product->id,
                        'url' => $imagePath,
                        'file_id' => $fileId,
                        'message' => 'Google Drive file is not publicly accessible. Please share the file with "Anyone with the link" permission in Google Drive.',
                    ]);
                } else {
                    Log::warning('Variant image import failed', [
                        'variant_id' => $variant->id,
                        'product_id' => $product->id,
                        'url' => $imagePath,
                        'message' => 'Failed to download or resolve image path.',
                    ]);
                }
                continue;
            }
    
            // Add resolved path to stored paths
            // This will create a separate record for each occurrence, even if resolved path is same
            $storedPaths[] = $resolvedPath;
        }
    
        // Replace existing images ONLY if we successfully imported at least one
        if (!empty($storedPaths)) {
            // Delete existing variant images
            $variant->images()->delete();
    
            // Create new images
            foreach ($storedPaths as $index => $path) {
                ProductImage::create([
                    'product_id'          => $product->id,
                    'product_variant_id'  => $variant->id,
                    'image_path'          => $path,
                    'alt_text'            => null,
                    'sort_order'          => $index,
                    'is_primary'          => $index === 0,
                ]);
            }
    
            // Legacy support - update variant's image field
            $variant->update(['image' => $storedPaths[0]]);
            
            Log::info('Variant images imported successfully', [
                'variant_id' => $variant->id,
                'product_id' => $product->id,
                'image_count' => count($storedPaths),
                'skipped_duplicates' => $skippedCount,
            ]);
        } else {
            Log::warning('No variant images were successfully imported', [
                'variant_id' => $variant->id,
                'product_id' => $product->id,
                'attempted_count' => count($imagePaths),
                'failed_count' => $failedCount,
                'skipped_count' => $skippedCount,
            ]);
        }
    }
    

    /**
     * Sync product images from import rows.
     *
     * @param  array<int,array<string,mixed>>  $imageRows
     */
    private function syncImages(Product $product, array $imageRows): void
    {
        if (empty($imageRows)) {
            return;
        }

        // Remove existing product-level images before re-importing
        $product->images()->delete();

        $sortOrder = 0;

        foreach ($imageRows as $row) {
            $pathInput = $row['image_path_or_url'] ?? $row['image_path'] ?? null;
            $resolvedPath = $this->resolveImagePath($pathInput);

            if (!$resolvedPath) {
                continue;
            }

            $isPrimary = $this->toBoolean($row['is_primary_(0_or_1)'] ?? $row['is_primary'] ?? null, false);
            $sortOrderValue = $row['sort_order'] ?? $sortOrder;

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $resolvedPath,
                'alt_text' => $row['alt_text'] ?? null,
                'sort_order' => is_numeric($sortOrderValue) ? (int) $sortOrderValue : $sortOrder,
                'is_primary' => $isPrimary,
            ]);

            $sortOrder++;
        }
    }

    /**
     * Attempt to resolve the image path, downloading remote assets when necessary.
     */
    private function resolveImagePath(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $path = trim((string) $value);
        if ($path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            // If the URL is a Google Drive share link, try to get a better download URL
            $lowerPath = strtolower($path);
            if (strpos($lowerPath, 'drive.google.com') !== false) {
                $fileId = $this->extractGoogleDriveFileId($path);
                if ($fileId) {
                    $betterUrl = $this->downloadGoogleDriveFile($fileId);
                    if ($betterUrl) {
                        $path = $betterUrl;
                    } else {
                        // Fallback to standard download URL
                        $path = 'https://drive.google.com/uc?export=download&id=' . $fileId;
                    }
                }
            }
            try {
                // Reduced timeout for faster failure detection (10 seconds instead of 30)
                $response = Http::timeout(10)
                    ->withOptions([
                        'allow_redirects' => true,
                        'verify' => false,
                        'connect_timeout' => 5, // Connection timeout
                    ])
                    ->get($path);
                
                if (!$response->successful()) {
                    Log::warning('ProductImportService@resolveImagePath - HTTP request failed', [
                        'url' => $path,
                        'status' => $response->status(),
                    ]);
                    return null;
                }

                $contentType = $response->header('Content-Type') ?? '';
                $body = $response->body();

                // Check if we got HTML instead of an image (Google Drive confirmation page)
                if (stripos($contentType, 'text/html') !== false || 
                    (stripos($body, '<html') !== false && stripos($body, 'drive.google.com') !== false)) {
                    
                    // Try to extract confirmation token from Google Drive confirmation page
                    if (stripos($path, 'drive.google.com') !== false) {
                        // Extract file ID if not already in URL
                        $fileId = $this->extractGoogleDriveFileId($path);
                        if ($fileId) {
                            // Try to extract confirmation token from the HTML
                            if (preg_match('#name="confirm"\s+value="([^"]+)"#', $body, $m) || 
                                preg_match('#confirm=([a-zA-Z0-9_-]+)#', $body, $m)) {
                                $confirmToken = $m[1];
                                $confirmUrl = 'https://drive.google.com/uc?export=download&confirm=' . $confirmToken . '&id=' . $fileId;
                                
                                Log::info('ProductImportService: Retrying with confirmation token', [
                                    'file_id' => $fileId,
                                    'confirm_url' => $confirmUrl,
                                ]);
                                
                                // Reduced timeout for faster failure detection
                                $response = Http::timeout(10)
                                    ->withOptions([
                                        'allow_redirects' => true,
                                        'verify' => false,
                                        'connect_timeout' => 5,
                                    ])
                                    ->get($confirmUrl);
                                
                                $contentType = $response->header('Content-Type') ?? '';
                                $body = $response->body();
                            }
                            
                            // If still HTML, try Googleusercontent direct access (works for publicly shared files)
                            if (stripos($contentType, 'text/html') !== false) {
                                $directUrl = 'https://lh3.googleusercontent.com/d/' . $fileId;
                                Log::info('ProductImportService: Trying Googleusercontent direct access', [
                                    'file_id' => $fileId,
                                    'direct_url' => $directUrl,
                                ]);
                                
                                try {
                                    // Reduced timeout for faster failure detection
                                    $directResponse = Http::timeout(10)
                                        ->withOptions([
                                            'allow_redirects' => true,
                                            'verify' => false,
                                            'connect_timeout' => 5,
                                        ])
                                        ->get($directUrl);
                                    
                                    $directContentType = $directResponse->header('Content-Type') ?? '';
                                    $directBody = $directResponse->body();
                                    
                                    // If this works, use it
                                    if (stripos($directContentType, 'image/') !== false && 
                                        stripos($directContentType, 'text/html') === false) {
                                        $response = $directResponse;
                                        $contentType = $directContentType;
                                        $body = $directBody;
                                        $path = $directUrl;
                                    } else {
                                        // Try view URL as another fallback
                                        $viewUrl = 'https://drive.google.com/uc?export=view&id=' . $fileId;
                                        Log::info('ProductImportService: Trying view URL as last resort', [
                                            'file_id' => $fileId,
                                        ]);
                                        
                                        // Reduced timeout for faster failure detection
                                $response = Http::timeout(10)
                                    ->withOptions([
                                        'allow_redirects' => true,
                                        'verify' => false,
                                        'connect_timeout' => 5,
                                    ])
                                            ->get($viewUrl);
                                        
                                        $contentType = $response->header('Content-Type') ?? '';
                                        $body = $response->body();
                                    }
                                } catch (Throwable $e) {
                                    // If direct access fails, try view URL
                                    $viewUrl = 'https://drive.google.com/uc?export=view&id=' . $fileId;
                                    Log::info('ProductImportService: Trying view URL after direct access failed', [
                                        'file_id' => $fileId,
                                        'error' => $e->getMessage(),
                                    ]);
                                    
                                    // Reduced timeout for faster failure detection
                                    $response = Http::timeout(10)
                                        ->withOptions([
                                            'allow_redirects' => true,
                                            'verify' => false,
                                            'connect_timeout' => 5,
                                        ])
                                        ->get($viewUrl);
                                    
                                    $contentType = $response->header('Content-Type') ?? '';
                                    $body = $response->body();
                                }
                            }
                        }
                    }
                    
                    // If we still have HTML after all attempts, provide helpful error message
                    if (stripos($contentType, 'text/html') !== false) {
                        $fileId = $this->extractGoogleDriveFileId($path);
                        $errorMsg = 'Google Drive file is not publicly accessible or requires authentication.';
                        if ($fileId) {
                            $errorMsg .= ' File ID: ' . $fileId . '. Please ensure the file is shared with "Anyone with the link" permission.';
                        }
                        
                        Log::warning('ProductImportService@resolveImagePath - Google Drive file not accessible', [
                            'url' => $path,
                            'file_id' => $fileId,
                            'content_type' => $contentType,
                            'message' => $errorMsg,
                        ]);
                        return null;
                    }
                }

                // Determine extension from content type or URL
                $extension = $this->getExtensionFromContentType($contentType);
                if (!$extension) {
                    $extension = pathinfo(parse_url($path, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
                }
                $filename = 'products/imports/' . Str::random(12) . '.' . strtolower($extension);

                Storage::disk('public')->put($filename, $body);
                
                // Validate downloaded content is a valid image
                $fullCheckPath = storage_path('app/public/' . $filename);
                $imageInfo = @getimagesize($fullCheckPath);
                if ($imageInfo === false) {
                    // Not a valid image, remove and skip
                    Storage::disk('public')->delete($filename);
                    Log::warning('ProductImportService@resolveImagePath - Downloaded file is not a valid image', [
                        'path' => $path,
                        'storage_path' => $filename,
                        'content_type' => $contentType,
                    ]);
                    return null;
                }
                
                Log::info('ProductImportService@resolveImagePath - Image downloaded and stored', [
                    'url' => $path,
                    'storage_path' => $filename,
                    'content_type' => $contentType,
                    'image_size' => $imageInfo[0] . 'x' . $imageInfo[1],
                ]);

                return $filename;
            } catch (Throwable $exception) {
                Log::warning('ProductImportService@resolveImagePath - Failed to download remote image', [
                    'url' => $path,
                    'message' => $exception->getMessage(),
                ]);
                return null;
            }
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/'));
        }

        if (Storage::disk('public')->exists($normalized)) {
            return $normalized;
        }

        // If the file does not exist we still store the relative path,
        // allowing admins to fix the asset later.
        return $normalized;
    }

    /**
     * Extract Google Drive file ID from various URL formats.
     */
    private function extractGoogleDriveFileId(string $url): ?string
    {
        // Pattern 1: /file/d/FILE_ID/view
        if (preg_match('#/file/d/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }
        
        // Pattern 2: /d/FILE_ID/
        if (preg_match('#/d/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }
        
        // Pattern 3: id=FILE_ID
        if (preg_match('#[?&]id=([a-zA-Z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }
        
        return null;
    }

    /**
     * Get the best download URL for a Google Drive file ID.
     * Returns a URL that should work for downloading the file.
     */
    private function downloadGoogleDriveFile(string $fileId): ?string
    {
        // Try to get a direct download URL by checking if the file is publicly accessible
        // Method 1: Standard download URL
        $downloadUrl = 'https://drive.google.com/uc?export=download&id=' . $fileId;
        
        // First, try a HEAD request to see if we get redirected or get HTML
        try {
            $headResponse = Http::timeout(10)
                                    ->withOptions([
                                        'allow_redirects' => true,
                                        'verify' => false,
                                        'connect_timeout' => 5,
                                    ])
                ->head($downloadUrl);
            
            $contentType = $headResponse->header('Content-Type') ?? '';
            
            // If we get HTML, we need to handle the confirmation page
            if (stripos($contentType, 'text/html') !== false) {
                // Try to get the page and extract the confirmation link
                $pageResponse = Http::timeout(10)
                                    ->withOptions([
                                        'allow_redirects' => true,
                                        'verify' => false,
                                        'connect_timeout' => 5,
                                    ])
                    ->get($downloadUrl);
                
                $body = $pageResponse->body();
                
                // Look for the confirmation link in the HTML
                // Google Drive confirmation pages often have: href="/uc?export=download&confirm=XXX&id=FILE_ID"
                if (preg_match('#href=["\']([^"\']*uc\?[^"\']*export=download[^"\']*confirm=[^"\']*id=' . preg_quote($fileId, '#') . '[^"\']*)["\']#', $body, $m)) {
                    $confirmUrl = html_entity_decode($m[1]);
                    if (!str_starts_with($confirmUrl, 'http')) {
                        $confirmUrl = 'https://drive.google.com' . ltrim($confirmUrl, '/');
                    }
                    
                    Log::info('ProductImportService: Found Google Drive confirmation link', [
                        'file_id' => $fileId,
                        'confirm_url' => $confirmUrl,
                    ]);
                    
                    return $confirmUrl;
                }
                
                // Alternative: Try the view URL which sometimes works without confirmation
                $viewUrl = 'https://drive.google.com/uc?export=view&id=' . $fileId;
                Log::info('ProductImportService: Using view URL as fallback for Google Drive', [
                    'file_id' => $fileId,
                ]);
                
                return $viewUrl;
            }
            
            // If content type looks good, return the download URL
            return $downloadUrl;
            
        } catch (Throwable $exception) {
            Log::warning('ProductImportService: Error checking Google Drive file', [
                'file_id' => $fileId,
                'message' => $exception->getMessage(),
            ]);
            
            // Return the download URL anyway, let the main download logic handle it
            return $downloadUrl;
        }
    }

    /**
     * Get file extension from content type.
     */
    private function getExtensionFromContentType(string $contentType): ?string
    {
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tiff',
        ];
        
        $contentType = strtolower(trim(explode(';', $contentType)[0]));
        return $mimeToExt[$contentType] ?? null;
    }

    /**
     * Decode JSON column safely.
     */
    private function decodeJsonColumn(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Generate a fallback variant name from attributes.
     */
    private function generateVariantNameFromAttributes(?string $attributesJson, string $fallback): string
    {
        $attributes = $this->decodeJsonColumn($attributesJson);
        if (empty($attributes)) {
            return $fallback;
        }

        $parts = [];
        foreach ($attributes as $value) {
            if (is_array($value)) {
                $parts[] = Arr::get($value, 'value', implode(' ', $value));
            } else {
                $parts[] = (string) $value;
            }
        }

        $parts = array_filter(array_map('trim', $parts));

        return !empty($parts) ? implode(' - ', $parts) : $fallback;
    }

    /**
     * Convert value to boolean flag.
     */
    private function toBoolean(mixed $value, bool $default = false): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower((string) $value);

        return in_array($normalized, ['1', 'true', 'yes', 'y', 'on'], true);
    }

    /**
     * Normalize stock status.
     */
    private function normalizeStockStatus(?string $value): string
    {
        $value = strtolower((string) ($value ?? 'in_stock'));

        return in_array($value, $this->allowedStockStatuses, true) ? $value : 'in_stock';
    }

    /**
     * Normalize discount type.
     */
    private function normalizeDiscountType(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = strtolower(trim($value));
        return in_array($value, ['percentage', 'amount'], true) ? $value : null;
    }

    /**
     * Get or create the default "Other" brand.
     */
    private function getDefaultBrand(): Brand
    {
        if ($this->cachedDefaultBrand) {
            return $this->cachedDefaultBrand;
        }

        $brand = Brand::firstOrCreate(
            ['slug' => self::DEFAULT_BRAND_SLUG],
            ['name' => 'Other', 'status' => true]
        );

        return $this->cachedDefaultBrand = $brand;
    }
}



