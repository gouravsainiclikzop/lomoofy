<?php

declare(strict_types=1);

use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel to access models
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$outputDir = realpath(__DIR__ . '/../public/import-templates');

if ($outputDir === false) {
    fwrite(STDERR, "Unable to resolve public/import-templates directory.\n");
    exit(1);
}

$filePath = $outputDir . DIRECTORY_SEPARATOR . 'products-template.xlsx';

// Fetch dynamic data from database
$categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get(['name', 'slug']);
$brands = \App\Models\Brand::where('is_active', true)->orderBy('name')->get(['name', 'slug']);

// Prepare dropdown options
$categoryOptions = $categories->pluck('slug')->toArray();
$brandOptions = $brands->pluck('slug')->toArray();
$statusOptions = ['published', 'hidden'];
$gstTypeOptions = ['0', '1'];
$gstPercentageOptions = ['0', '3', '5', '12', '18', '28'];
$featuredOptions = ['0', '1'];
$discountTypeOptions = ['percentage', 'amount'];
$discountActiveOptions = ['0', '1'];
$isActiveOptions = ['0', '1'];

// Create new spreadsheet with PhpSpreadsheet for better dropdown support
$spreadsheet = new Spreadsheet();

// Products sheet
$productsSheet = $spreadsheet->getActiveSheet();
$productsSheet->setTitle('Products');

// Set headers
$productHeaders = [
    'Product Name',
    'SEO Slug', 
    'Status',
    'Short Description',
    'Brand Slugs (comma separated)',
    'Category Slugs (comma separated)',
    'Tag List',
    'Featured',
    'GST Type (0 or 1)',
    'GST Percentage'
];

// Add headers
$productsSheet->fromArray($productHeaders, null, 'A1');

// Style headers
$productsSheet->getStyle('A1:J1')->getFont()->setBold(true);
$productsSheet->getStyle('A1:J1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6FA');

// Add sample data
$productsSheet->fromArray([
    'Sample T-Shirt',
    'sample-t-shirt', 
    'published',
    'A comfortable cotton t-shirt for everyday wear',
    implode(', ', array_slice($brandOptions, 0, 2)), // Show first 2 brands as example
    implode(', ', array_slice($categoryOptions, 0, 2)), // Show first 2 categories as example
    'casual, cotton, comfortable',
    '1',
    '1', 
    '18'
], null, 'A2');

// Add data validation (dropdowns)
// Status dropdown (Column C)
if (!empty($statusOptions)) {
    $validation = $productsSheet->getCell('C2')->getDataValidation();
    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validation->setAllowBlank(false);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setShowDropDown(true);
    $validation->setErrorTitle('Input error');
    $validation->setError('Value is not in list.');
    $validation->setPromptTitle('Pick from list');
    $validation->setPrompt('Please pick a value from the drop-down list.');
    $validation->setFormula1('"' . implode(',', $statusOptions) . '"');
    
    // Apply to more rows
    $productsSheet->setDataValidation('C2:C1000', clone $validation);
}

// Featured dropdown (Column H)
if (!empty($featuredOptions)) {
    $validation = $productsSheet->getCell('H2')->getDataValidation();
    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validation->setAllowBlank(false);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setShowDropDown(true);
    $validation->setErrorTitle('Input error');
    $validation->setError('Value is not in list.');
    $validation->setPromptTitle('Pick from list');
    $validation->setPrompt('Please pick a value from the drop-down list.');
    $validation->setFormula1('"' . implode(',', $featuredOptions) . '"');
    
    $productsSheet->setDataValidation('H2:H1000', clone $validation);
}

// GST Type dropdown (Column I)
if (!empty($gstTypeOptions)) {
    $validation = $productsSheet->getCell('I2')->getDataValidation();
    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validation->setAllowBlank(false);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setShowDropDown(true);
    $validation->setErrorTitle('Input error');
    $validation->setError('Value is not in list.');
    $validation->setPromptTitle('Pick from list');
    $validation->setPrompt('Please pick a value from the drop-down list.');
    $validation->setFormula1('"' . implode(',', $gstTypeOptions) . '"');
    
    $productsSheet->setDataValidation('I2:I1000', clone $validation);
}

// GST Percentage dropdown (Column J)
if (!empty($gstPercentageOptions)) {
    $validation = $productsSheet->getCell('J2')->getDataValidation();
    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validation->setAllowBlank(false);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setShowDropDown(true);
    $validation->setErrorTitle('Input error');
    $validation->setError('Value is not in list.');
    $validation->setPromptTitle('Pick from list');
    $validation->setPrompt('Please pick a value from the drop-down list.');
    $validation->setFormula1('"' . implode(',', $gstPercentageOptions) . '"');
    
    $productsSheet->setDataValidation('J2:J1000', clone $validation);
}

// Auto-size columns
foreach (range('A', 'J') as $col) {
    $productsSheet->getColumnDimension($col)->setAutoSize(true);
}

// Variants sheet
$variantsSheet = $spreadsheet->createSheet();
$variantsSheet->setTitle('Variants');

// Set headers
$variantHeaders = [
    'Product Name',
    'Product Slug',
    'Brand Slugs (comma separated)',
    'Category Slugs (comma separated)',
    'Variant SKU',
    'Variant Name',
    'Price',
    'Sale Price',
    'Is Active (0 or 1)',
    'Attributes (key:value format)',
    'Discount Type',
    'Discount Value',
    'Discount Active',
    'Barcode',
    'Low Stock Threshold',
    'Highlights Details (structured format)',
    'Detailed Description'
];

// Add headers
$variantsSheet->fromArray($variantHeaders, null, 'A1');

// Style headers
$variantsSheet->getStyle('A1:Q1')->getFont()->setBold(true);
$variantsSheet->getStyle('A1:Q1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6FA');

// Add sample data
$sampleVariants = [
    [
        'Sample T-Shirt',
        'sample-t-shirt',
        implode(', ', array_slice($brandOptions, 0, 1)), // Show first brand as example
        implode(', ', array_slice($categoryOptions, 0, 1)), // Show first category as example
        'TSHIRT-RED-M',
        'Red - Medium',
        '29.99',
        '24.99',
        '1',
        'color:red|size:M|material:cotton|style:casual',
        'percentage',
        '17',
        '1',
        'TSHIRT-RED-M-BC',
        '5',
        'Key Features>>100% premium cotton fabric|Soft and breathable material|Comfortable for all-day wear##Care Instructions>>Machine wash cold|Do not bleach|Tumble dry low',
        'Red colored t-shirt in medium size'
    ],
    [
        'Sample T-Shirt',
        '',
        '', // Empty for subsequent variants of same product
        '', // Empty for subsequent variants of same product
        'TSHIRT-RED-L',
        'Red - Large',
        '29.99',
        '24.99',
        '1',
        'color:red|size:L|material:cotton|style:casual',
        '',
        '',
        '',
        'TSHIRT-RED-L-BC',
        '5',
        'Key Features>>100% premium cotton fabric|Soft and breathable material|Comfortable for all-day wear##Care Instructions>>Machine wash cold|Do not bleach|Tumble dry low',
        'Red colored t-shirt in large size'
    ],
    [
        'Sample T-Shirt',
        '',
        '', // Empty for subsequent variants of same product
        '', // Empty for subsequent variants of same product
        'TSHIRT-BLUE-M',
        'Blue - Medium',
        '29.99',
        '',
        '1',
        'color:blue|size:M|material:cotton|style:casual',
        '',
        '',
        '',
        'TSHIRT-BLUE-M-BC',
        '5',
        'Key Features>>100% premium cotton fabric|Soft and breathable material|Comfortable for all-day wear##Care Instructions>>Machine wash cold|Do not bleach|Tumble dry low',
        'Blue colored t-shirt in medium size'
    ]
];

$variantsSheet->fromArray($sampleVariants, null, 'A2');

// Add data validation for variants
// Is Active dropdown (Column I - shifted by 2 due to brand/category columns)
if (!empty($isActiveOptions)) {
    $validation = $variantsSheet->getCell('I2')->getDataValidation();
    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validation->setAllowBlank(false);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setShowDropDown(true);
    $validation->setErrorTitle('Input error');
    $validation->setError('Value is not in list.');
    $validation->setPromptTitle('Pick from list');
    $validation->setPrompt('Please pick a value from the drop-down list.');
    $validation->setFormula1('"' . implode(',', $isActiveOptions) . '"');
    
    $variantsSheet->setDataValidation('I2:I1000', clone $validation);
}

// Discount Type dropdown (Column K - shifted by 2 due to brand/category columns)
if (!empty($discountTypeOptions)) {
    $validation = $variantsSheet->getCell('K2')->getDataValidation();
    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validation->setAllowBlank(true);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setShowDropDown(true);
    $validation->setErrorTitle('Input error');
    $validation->setError('Value is not in list.');
    $validation->setPromptTitle('Pick from list');
    $validation->setPrompt('Please pick a value from the drop-down list.');
    $validation->setFormula1('"' . implode(',', $discountTypeOptions) . '"');
    
    $variantsSheet->setDataValidation('K2:K1000', clone $validation);
}

// Discount Active dropdown (Column M - shifted by 2 due to brand/category columns)
if (!empty($discountActiveOptions)) {
    $validation = $variantsSheet->getCell('M2')->getDataValidation();
    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validation->setAllowBlank(true);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setShowDropDown(true);
    $validation->setErrorTitle('Input error');
    $validation->setError('Value is not in list.');
    $validation->setPromptTitle('Pick from list');
    $validation->setPrompt('Please pick a value from the drop-down list.');
    $validation->setFormula1('"' . implode(',', $discountActiveOptions) . '"');
    
    $variantsSheet->setDataValidation('M2:M1000', clone $validation);
}

// Auto-size columns
foreach (range('A', 'Q') as $col) {
    $variantsSheet->getColumnDimension($col)->setAutoSize(true);
}

// Images sheet
$imagesSheet = $spreadsheet->createSheet();
$imagesSheet->setTitle('Images');

// Set headers
$imageHeaders = [
    'Product Name',
    'Product Slug',
    'Image Path or URL',
    'Is Primary (0 or 1)',
    'Sort Order',
    'Alt Text'
];

// Add headers
$imagesSheet->fromArray($imageHeaders, null, 'A1');

// Style headers
$imagesSheet->getStyle('A1:F1')->getFont()->setBold(true);
$imagesSheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6FA');

// Add sample data
$sampleImages = [
    [
        'Sample T-Shirt',
        'sample-t-shirt',
        'products/sample-t-shirt-front.jpg',
        '1',
        '0',
        'Sample T-Shirt Front View'
    ],
    [
        'Sample T-Shirt',
        '',
        'products/sample-t-shirt-back.jpg',
        '0',
        '1',
        'Sample T-Shirt Back View'
    ],
    [
        'Sample T-Shirt',
        '',
        'products/sample-t-shirt-side.jpg',
        '0',
        '2',
        'Sample T-Shirt Side View'
    ]
];

$imagesSheet->fromArray($sampleImages, null, 'A2');

// Add data validation for images
// Is Primary dropdown (Column D)
$isPrimaryOptions = ['0', '1'];
if (!empty($isPrimaryOptions)) {
    $validation = $imagesSheet->getCell('D2')->getDataValidation();
    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validation->setAllowBlank(false);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setShowDropDown(true);
    $validation->setErrorTitle('Input error');
    $validation->setError('Value is not in list.');
    $validation->setPromptTitle('Pick from list');
    $validation->setPrompt('Please pick a value from the drop-down list.');
    $validation->setFormula1('"' . implode(',', $isPrimaryOptions) . '"');
    
    $imagesSheet->setDataValidation('D2:D1000', clone $validation);
}

// Auto-size columns
foreach (range('A', 'F') as $col) {
    $imagesSheet->getColumnDimension($col)->setAutoSize(true);
}

// Create reference sheet for dynamic data
$referenceSheet = $spreadsheet->createSheet();
$referenceSheet->setTitle('Reference Data');

// Add reference data
$referenceSheet->setCellValue('A1', 'Available Categories:');
$referenceSheet->getStyle('A1')->getFont()->setBold(true);
$categoryRow = 2;
foreach ($categories as $category) {
    $referenceSheet->setCellValue('A' . $categoryRow, $category->slug . ' (' . $category->name . ')');
    $categoryRow++;
}

$referenceSheet->setCellValue('C1', 'Available Brands:');
$referenceSheet->getStyle('C1')->getFont()->setBold(true);
$brandRow = 2;
foreach ($brands as $brand) {
    $referenceSheet->setCellValue('C' . $brandRow, $brand->slug . ' (' . $brand->name . ')');
    $brandRow++;
}

// Add instructions
$referenceSheet->setCellValue('E1', 'Instructions:');
$referenceSheet->getStyle('E1')->getFont()->setBold(true);
$referenceSheet->setCellValue('E2', '1. Use dropdown arrows in cells to select values');
$referenceSheet->setCellValue('E3', '2. For brands/categories, use the slug values (not names)');
$referenceSheet->setCellValue('E4', '3. Multiple brands/categories: separate with commas');
$referenceSheet->setCellValue('E5', '4. Example: "clothing, mens-clothing" for categories');
$referenceSheet->setCellValue('E6', '5. GST Type: 0=Exclusive, 1=Inclusive');
$referenceSheet->setCellValue('E7', '6. Featured: 0=No, 1=Yes');

// Auto-size reference columns
foreach (range('A', 'F') as $col) {
    $referenceSheet->getColumnDimension($col)->setAutoSize(true);
}

// Set active sheet back to Products
$spreadsheet->setActiveSheetIndex(0);

// Save the file
$writer = new Xlsx($spreadsheet);
$writer->save($filePath);

fwrite(STDOUT, "Regenerated products-template.xlsx with dynamic dropdowns at {$filePath}\n");
fwrite(STDOUT, "Categories loaded: " . count($categories) . "\n");
fwrite(STDOUT, "Brands loaded: " . count($brands) . "\n");


