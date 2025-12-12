<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
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
            Log::error('ProductImportService@import - Failed to parse file', [
                'file' => $originalName,
                'extension' => $extension,
                'message' => $exception->getMessage(),
            ]);

            $result['errors'][] = [
                'row' => null,
                'identifier' => $originalName,
                'message' => $exception->getMessage(),
            ];

            return $result;
        }

        $products = $dataset['products'] ?? [];
        $variants = $this->groupRowsByKey($dataset['variants'] ?? [], 'product_sku');
        $images = $this->groupRowsByKey($dataset['images'] ?? [], 'product_sku');

        foreach ($products as $index => $productRow) {
            $rowNumber = $productRow['_row'] ?? ($index + 2); // header occupies first row

            $sku = trim((string) ($productRow['product_sku'] ?? ''));
            if ($sku === '') {
                $result['errors'][] = [
                    'row' => $rowNumber,
                    'identifier' => null,
                    'message' => 'Product SKU is required.',
                ];
                $result['skipped']++;
                continue;
            }

            try {
                $importOutcome = DB::transaction(function () use ($sku, $productRow, $variants, $images) {
                    return $this->upsertProduct($sku, $productRow, $variants[$sku] ?? [], $images[$sku] ?? []);
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
                    'sku' => $sku,
                    'row' => $rowNumber,
                    'message' => $exception->getMessage(),
                ]);

                $result['errors'][] = [
                    'row' => $rowNumber,
                    'identifier' => $sku,
                    'message' => $exception->getMessage(),
                ];
                $result['skipped']++;
            }
        }

        $missingVariantKeys = array_diff(array_keys($variants), array_column($products, 'product_sku'));
        foreach ($missingVariantKeys as $missingSku) {
            $result['warnings'][] = "Variants provided for SKU {$missingSku}, but the product sheet does not contain this SKU. The variants were ignored.";
        }

        $missingImageKeys = array_diff(array_keys($images), array_column($products, 'product_sku'));
        foreach ($missingImageKeys as $missingSku) {
            $result['warnings'][] = "Images provided for SKU {$missingSku}, but the product sheet does not contain this SKU. The images were ignored.";
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
                    Log::info('ProductImportService@parseSheet - Unknown sheet skipped', [
                        'sheet' => $sheet->getName(),
                    ]);
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
            if ($rowIndex === 1) {
                $headers = $this->normalizeHeaders($cells);
                continue;
            }

            $assocRow = $this->mapRow($headers, $cells);
            if ($this->rowIsEmpty($assocRow)) {
                continue;
            }

            $assocRow['_row'] = $rowIndex;
            $rows[] = $assocRow;
        }

        fclose($handle);

        return [
            'products' => $rows,
            'variants' => [],
            'images' => [],
        ];
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
     * Upsert a product and its related entities.
     *
     * @param  array<string,mixed>  $productRow
     * @param  array<int,array<string,mixed>>  $variantRows
     * @param  array<int,array<string,mixed>>  $imageRows
     */
    private function upsertProduct(string $sku, array $productRow, array $variantRows, array $imageRows): string
    {
        $product = Product::with(['brands', 'categories', 'variants', 'images'])->where('sku', $sku)->first();
        $isNew = false;

        if (!$product) {
            $product = new Product();
            $product->sku = $sku;
            $isNew = true;
        }

        $name = $productRow['product_name'] ?? $product->name ?? null;
        if (!$name) {
            throw new \RuntimeException('Product Name is required.');
        }

        $type = strtolower((string) ($productRow['product_type'] ?? $product->type ?? 'simple'));
        if (!in_array($type, $this->allowedProductTypes, true)) {
            $type = 'simple';
        }

        $status = strtolower((string) ($productRow['status'] ?? $product->status ?? 'hidden'));
        if (!in_array($status, $this->allowedStatuses, true)) {
            $status = 'hidden';
        }

        $price = $this->toDecimal($productRow['price'] ?? null);
        $salePrice = $this->toDecimal($productRow['sale_price'] ?? null);

        $slugInput = $productRow['seo_slug'] ?? $productRow['product_slug'] ?? $productRow['slug'] ?? $name;
        $slugCandidate = Str::slug((string) $slugInput);
        $slug = $this->generateUniqueSlug($slugCandidate, $product->id);

        $product->fill([
            'name' => $name,
            'slug' => $slug,
            'description' => $productRow['description'] ?? $product->description,
            'short_description' => $productRow['short_description'] ?? $product->short_description,
            'type' => $type,
            'price' => $price,
            'sale_price' => $salePrice,
            'status' => $status,
            'tags' => $this->normalizeTags($productRow['tag_list'] ?? null),
        ]);

        $product->save();

        $brandIds = $this->resolveBrandIds($productRow['brand_slugs_(comma_separated)'] ?? $productRow['brand_slugs'] ?? null);
        $this->syncProductBrands($product, $brandIds);

        // Resolve category (supports unified hierarchy - use deepest category if multiple provided)
        // Support both legacy column names for backward compatibility
        $categorySlug = $productRow['category_slug'] 
            ?? $productRow['category_slugs_(comma_separated)'] 
            ?? $productRow['category_slugs'] 
            ?? $productRow['subcategory_slugs_(comma_separated)'] 
            ?? $productRow['subcategory_slugs'] 
            ?? null;
        
        $categoryIds = $this->resolveCategoryIds($categorySlug);
        
        // Use the deepest category (last in list) or first if only one
        $categoryId = !empty($categoryIds) ? end($categoryIds) : null;
        $product->category_id = $categoryId;
        $product->save();

        $this->syncVariants($product, $variantRows);

        $this->syncImages($product, $imageRows);

        return $isNew ? 'created' : 'updated';
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

        // Support both comma and pipe delimiters
        $normalized = str_replace('|', ',', $list);
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

        $product->brand_id = $brandIds[0];
        $product->save();

        $pivotData = [];
        foreach ($brandIds as $index => $brandId) {
            $pivotData[$brandId] = [
                'is_primary' => $index === 0,
                'sort_order' => $index,
            ];
        }

        $product->brands()->sync($pivotData);
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
            $variantSku = trim((string) ($row['variant_sku'] ?? ''));
            if ($variantSku === '') {
                $variantSku = "{$product->sku}-V" . ($sortOrder + 1);
            }

            $processedSkus[] = $variantSku;

            $variant = $existingVariants->get($variantSku) ?? new ProductVariant();
            $isNew = !$variant->exists;

            $variantName = $row['variant_name'] ?? ($variant->name ?? null);
            if (!$variantName) {
                $variantName = $this->generateVariantNameFromAttributes($row['attributes_json'] ?? null, $variantSku);
            }

            $attributes = $this->decodeJsonColumn($row['attributes_json'] ?? null);
            $measurements = $this->decodeJsonColumn($row['measurements_json'] ?? null);

            $variant->fill([
                'product_id' => $product->id,
                'sku' => $variantSku,
                'name' => $variantName,
                'price' => $this->toDecimal($row['price'] ?? null) ?? 0,
                'sale_price' => $this->toDecimal($row['sale_price'] ?? null),
                'cost_price' => $this->toDecimal($row['cost_price'] ?? null),
                'stock_quantity' => (int) ($row['stock_quantity'] ?? $variant->stock_quantity ?? 0),
                'manage_stock' => $this->toBoolean($row['manage_stock_(0_or_1)'] ?? $row['manage_stock'] ?? null, true),
                'stock_status' => $this->normalizeStockStatus($row['stock_status'] ?? $variant->stock_status ?? 'in_stock'),
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
            ]);

            $variant->save();
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
            try {
                $response = Http::timeout(10)->get($path);
                if (!$response->successful()) {
                    return null;
                }

                $extension = pathinfo(parse_url($path, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
                $filename = 'products/imports/' . Str::random(12) . '.' . strtolower($extension);

                Storage::disk('public')->put($filename, $response->body());

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


