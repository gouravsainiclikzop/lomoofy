<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    /**
     * Export brands to CSV or Excel
     */
    public function exportBrands(Request $request)
    {
        $format = $request->get('format', 'csv');
        $brands = Brand::orderBy('sort_order')->orderBy('name')->get();

        if ($format === 'xlsx') {
            return $this->exportBrandsToExcel($brands);
        }

        return $this->exportBrandsToCsv($brands);
    }

    /**
     * Export all categories (unlimited depth hierarchy) to CSV or Excel
     */
    public function exportCategories(Request $request)
    {
        $format = $request->get('format', 'csv');
        $categories = Category::with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($format === 'xlsx') {
            return $this->exportCategoriesToExcel($categories);
        }

        return $this->exportCategoriesToCsv($categories);
    }

    /**
     * Export products to CSV or Excel
     */
    public function exportProducts(Request $request)
    {
        $format = $request->get('format', 'csv');
        $products = Product::with(['brands', 'category.parent'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($format === 'xlsx') {
            return $this->exportProductsToExcel($products);
        }

        return $this->exportProductsToCsv($products);
    }

    /**
     * Export product variants to CSV or Excel
     */
    public function exportVariants(Request $request)
    {
        $format = $request->get('format', 'csv');
        $variants = ProductVariant::with('product')
            ->orderBy('product_id')
            ->orderBy('sort_order')
            ->get();

        if ($format === 'xlsx') {
            return $this->exportVariantsToExcel($variants);
        }

        return $this->exportVariantsToCsv($variants);
    }

    // CSV Export Methods

    private function exportBrandsToCsv($brands)
    {
        $filename = 'brands_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($brands) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['ID', 'Name', 'Slug', 'Description', 'Logo', 'Website', 'Is Active', 'Sort Order', 'Created At', 'Updated At']);
            
            // Data
            foreach ($brands as $brand) {
                fputcsv($file, [
                    $brand->id,
                    $brand->name,
                    $brand->slug,
                    $brand->description,
                    $brand->logo,
                    $brand->website,
                    $brand->is_active ? '1' : '0',
                    $brand->sort_order,
                    $brand->created_at,
                    $brand->updated_at,
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportCategoriesToCsv($categories)
    {
        $filename = 'categories_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($categories) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['ID', 'Name', 'Slug', 'Description', 'Parent Category Slug', 'Image', 'Is Active', 'Sort Order', 'Featured', 'Meta Title', 'Meta Description', 'Meta Keywords', 'Created At', 'Updated At']);
            
            // Data
            foreach ($categories as $category) {
                fputcsv($file, [
                    $category->id,
                    $category->name,
                    $category->slug,
                    $category->description,
                    $category->parent ? $category->parent->slug : '',
                    $category->image,
                    $category->is_active ? '1' : '0',
                    $category->sort_order,
                    $category->featured ? '1' : '0',
                    $category->meta_title,
                    $category->meta_description,
                    $category->meta_keywords,
                    $category->created_at,
                    $category->updated_at,
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }


    private function exportProductsToCsv($products)
    {
        $filename = 'products_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Product SKU',
                'Product Name',
                'Product Type',
                'Status',
                'Price',
                'Sale Price',
                'Brand Slugs (comma separated)',
                'Category Slug',
                'Category Path',
                'Tag List',
                'Description',
                'Short Description',
                'SKU',
                'Barcode',
                'Stock Quantity',
                'Stock Status',
                'Weight',
                'Length',
                'Width',
                'Height',
                'Featured',
                'Meta Title',
                'Meta Description',
                'Meta Keywords',
            ]);
            
            // Data
            foreach ($products as $product) {
                $brandSlugs = $product->brands->pluck('slug')->filter()->join(', ');
                $categorySlug = $product->category ? $product->category->slug : '';
                $categoryPath = $product->category_path ?? '';
                $tags = is_array($product->tags) ? implode(', ', $product->tags) : ($product->tags ?? '');
                
                fputcsv($file, [
                    $product->sku,
                    $product->name,
                    $product->type,
                    $product->status,
                    $product->price ?? '',
                    $product->sale_price ?? '',
                    $brandSlugs,
                    $categorySlug,
                    $categoryPath,
                    $tags,
                    $product->description ?? '',
                    $product->short_description ?? '',
                    $product->sku,
                    $product->barcode ?? '',
                    $product->stock_quantity ?? '',
                    $product->stock_status ?? '',
                    $product->weight ?? '',
                    $product->length ?? '',
                    $product->width ?? '',
                    $product->height ?? '',
                    $product->featured ? '1' : '0',
                    $product->meta_title ?? '',
                    $product->meta_description ?? '',
                    $product->meta_keywords ?? '',
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportVariantsToCsv($variants)
    {
        $filename = 'variants_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($variants) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Product SKU',
                'Variant SKU',
                'Variant Name',
                'Attributes (JSON)',
                'Price',
                'Sale Price',
                'Cost Price',
                'Stock Quantity',
                'Stock Status',
                'Weight',
                'Length',
                'Width',
                'Height',
                'Diameter',
                'Measurements (JSON)',
                'Is Active',
                'Sort Order',
            ]);
            
            // Data
            foreach ($variants as $variant) {
                $attributes = $variant->attributes ? json_encode($variant->attributes) : '';
                $measurements = $variant->measurements ? json_encode($variant->measurements) : '';
                
                fputcsv($file, [
                    $variant->product->sku ?? '',
                    $variant->sku,
                    $variant->name,
                    $attributes,
                    $variant->price ?? '',
                    $variant->sale_price ?? '',
                    $variant->cost_price ?? '',
                    $variant->stock_quantity ?? '',
                    $variant->stock_status ?? '',
                    $variant->weight ?? '',
                    $variant->length ?? '',
                    $variant->width ?? '',
                    $variant->height ?? '',
                    $variant->diameter ?? '',
                    $measurements,
                    $variant->is_active ? '1' : '0',
                    $variant->sort_order ?? '',
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    // Excel Export Methods

    private function exportBrandsToExcel($brands)
    {
        $filename = 'brands_export_' . date('Y-m-d_His') . '.xlsx';
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser($filename);

        // Headers
        $headerRow = WriterEntityFactory::createRowFromArray([
            'ID', 'Name', 'Slug', 'Description', 'Logo', 'Website', 'Is Active', 'Sort Order', 'Created At', 'Updated At'
        ]);
        $writer->addRow($headerRow);

        // Data
        foreach ($brands as $brand) {
            $row = WriterEntityFactory::createRowFromArray([
                $brand->id,
                $brand->name,
                $brand->slug,
                $brand->description,
                $brand->logo,
                $brand->website,
                $brand->is_active ? '1' : '0',
                $brand->sort_order,
                $brand->created_at,
                $brand->updated_at,
            ]);
            $writer->addRow($row);
        }

        $writer->close();
        exit;
    }

    private function exportCategoriesToExcel($categories)
    {
        $filename = 'categories_export_' . date('Y-m-d_His') . '.xlsx';
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser($filename);

        // Headers
        $headerRow = WriterEntityFactory::createRowFromArray([
            'ID', 'Name', 'Slug', 'Description', 'Parent Category Slug', 'Image', 'Is Active', 'Sort Order', 'Featured', 'Meta Title', 'Meta Description', 'Meta Keywords', 'Created At', 'Updated At'
        ]);
        $writer->addRow($headerRow);

        // Data
        foreach ($categories as $category) {
            $row = WriterEntityFactory::createRowFromArray([
                $category->id,
                $category->name,
                $category->slug,
                $category->description,
                $category->parent ? $category->parent->slug : '',
                $category->image,
                $category->is_active ? '1' : '0',
                $category->sort_order,
                $category->featured ? '1' : '0',
                $category->meta_title,
                $category->meta_description,
                $category->meta_keywords,
                $category->created_at,
                $category->updated_at,
            ]);
            $writer->addRow($row);
        }

        $writer->close();
        exit;
    }


    private function exportProductsToExcel($products)
    {
        $filename = 'products_export_' . date('Y-m-d_His') . '.xlsx';
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser($filename);

        // Headers
        $headerRow = WriterEntityFactory::createRowFromArray([
            'Product SKU',
            'Product Name',
            'Product Type',
            'Status',
            'Price',
            'Sale Price',
            'Brand Slugs (comma separated)',
            'Category Slug',
            'Category Path',
            'Tag List',
            'Description',
            'Short Description',
            'SKU',
            'Barcode',
            'Stock Quantity',
            'Stock Status',
            'Weight',
            'Length',
            'Width',
            'Height',
            'Featured',
            'Meta Title',
            'Meta Description',
            'Meta Keywords',
        ]);
        $writer->addRow($headerRow);

        // Data
        foreach ($products as $product) {
            $brandSlugs = $product->brands->pluck('slug')->filter()->join(', ');
            $categorySlug = $product->category ? $product->category->slug : '';
            $categoryPath = $product->category_path ?? '';
            $tags = is_array($product->tags) ? implode(', ', $product->tags) : ($product->tags ?? '');
            
            $row = WriterEntityFactory::createRowFromArray([
                $product->sku,
                $product->name,
                $product->type,
                $product->status,
                $product->price ?? '',
                $product->sale_price ?? '',
                $brandSlugs,
                $categorySlug,
                $categoryPath,
                $tags,
                $product->description ?? '',
                $product->short_description ?? '',
                $product->sku,
                $product->barcode ?? '',
                $product->stock_quantity ?? '',
                $product->stock_status ?? '',
                $product->weight ?? '',
                $product->length ?? '',
                $product->width ?? '',
                $product->height ?? '',
                $product->featured ? '1' : '0',
                $product->meta_title ?? '',
                $product->meta_description ?? '',
                $product->meta_keywords ?? '',
            ]);
            $writer->addRow($row);
        }

        $writer->close();
        exit;
    }

    private function exportVariantsToExcel($variants)
    {
        $filename = 'variants_export_' . date('Y-m-d_His') . '.xlsx';
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser($filename);

        // Headers
        $headerRow = WriterEntityFactory::createRowFromArray([
            'Product SKU',
            'Variant SKU',
            'Variant Name',
            'Attributes (JSON)',
            'Price',
            'Sale Price',
            'Cost Price',
            'Stock Quantity',
            'Stock Status',
            'Weight',
            'Length',
            'Width',
            'Height',
            'Diameter',
            'Measurements (JSON)',
            'Is Active',
            'Sort Order',
        ]);
        $writer->addRow($headerRow);

        // Data
        foreach ($variants as $variant) {
            $attributes = $variant->attributes ? json_encode($variant->attributes) : '';
            $measurements = $variant->measurements ? json_encode($variant->measurements) : '';
            
            $row = WriterEntityFactory::createRowFromArray([
                $variant->product->sku ?? '',
                $variant->sku,
                $variant->name,
                $attributes,
                $variant->price ?? '',
                $variant->sale_price ?? '',
                $variant->cost_price ?? '',
                $variant->stock_quantity ?? '',
                $variant->stock_status ?? '',
                $variant->weight ?? '',
                $variant->length ?? '',
                $variant->width ?? '',
                $variant->height ?? '',
                $variant->diameter ?? '',
                $measurements,
                $variant->is_active ? '1' : '0',
                $variant->sort_order ?? '',
            ]);
            $writer->addRow($row);
        }

        $writer->close();
        exit;
    }
}

