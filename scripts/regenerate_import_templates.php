<?php

declare(strict_types=1);

use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

require __DIR__ . '/../vendor/autoload.php';

$outputDir = realpath(__DIR__ . '/../public/import-templates');

if ($outputDir === false) {
    fwrite(STDERR, "Unable to resolve public/import-templates directory.\n");
    exit(1);
}

$filePath = $outputDir . DIRECTORY_SEPARATOR . 'products-template.xlsx';

$writer = WriterEntityFactory::createXLSXWriter();
$writer->openToFile($filePath);

$headerStyle = (new StyleBuilder())
    ->setFontBold()
    ->build();

// Products sheet
$writer->getCurrentSheet()->setName('Products');
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'Product SKU',
    'Product Name',
    'Product Type',
    'Status',
    'Price',
    'Sale Price',
    'Brand Slugs (comma separated)',
    'Category Slugs (comma separated)',
    'Subcategory Slugs (comma separated)',
    'Tag List',
    'Description',
    'Short Description',
], $headerStyle));
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'SKU-001',
    'Sample Product',
    'variable',
    'hidden',
    '100.00',
    '90.00',
    'other',
    'home-appliances|kitchen-appliances',
    'small-appliances|large-appliances',
    'tag1, tag2',
    '<p>Main content here</p>',
    'Quick summary',
]));

// Variants sheet
$writer->addNewSheetAndMakeItCurrent();
$writer->getCurrentSheet()->setName('Variants');
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'Product SKU',
    'Variant SKU',
    'Variant Name',
    'Attributes JSON',
    'Price',
    'Sale Price',
    'Cost Price',
    'Stock Quantity',
    'Manage Stock (0 or 1)',
    'Stock Status',
    'Is Active (0 or 1)',
    'Discount Type',
    'Discount Value',
    'Discount Active',
    'Measurements JSON',
    'Weight',
    'Length',
    'Width',
    'Height',
    'Diameter',
], $headerStyle));
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'SKU-001',
    'SKU-001-V1',
    'Variant Option 1',
    '{"color":"Black","storage":"128GB"}',
    '100.00',
    '90.00',
    '80.00',
    '10',
    '1',
    'in_stock',
    '1',
    'percentage',
    '10',
    '1',
    '[]',
    '',
    '',
    '',
    '',
    '',
]));

// Images sheet
$writer->addNewSheetAndMakeItCurrent();
$writer->getCurrentSheet()->setName('Images');
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'Product SKU',
    'Image Path or URL',
    'Is Primary (0 or 1)',
    'Sort Order',
    'Alt Text',
], $headerStyle));
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'SKU-001',
    'products/sample-primary.jpg',
    '1',
    '0',
    'Front profile',
]));
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'SKU-001',
    'products/sample-secondary.jpg',
    '0',
    '1',
    'Lifestyle photo',
]));

$writer->close();

fwrite(STDOUT, "Regenerated products-template.xlsx at {$filePath}\n");


