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
    'Product Name',
    'SEO Slug',
    'Status',
    'Short Description',
    'Description',
    'Brand Slugs (comma separated)',
    'Category Slugs (comma separated)',
    'Tag List',
    'Featured',
    'Requires Shipping (0 or 1)',
    'Free Shipping (0 or 1)',
    'GST Type (0 or 1)',
    'GST Percentage',
    'Meta Title',
    'Meta Description',
    'Meta Keywords',
], $headerStyle));
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'Sample T-Shirt',
    'sample-t-shirt',
    'published',
    'A comfortable cotton t-shirt for everyday wear',
    '<p>This is a high-quality cotton t-shirt that offers comfort and style. Perfect for casual wear.</p>',
    'other, brand-name',
    'clothing, mens-clothing',
    'casual, cotton, comfortable',
    '1',
    '1',
    '0',
    '1',
    '18',
    'Sample T-Shirt - Comfortable Cotton',
    'Buy our comfortable cotton t-shirt. Perfect for everyday wear.',
    't-shirt, cotton, casual, clothing',
]));

// Variants sheet
$writer->addNewSheetAndMakeItCurrent();
$writer->getCurrentSheet()->setName('Variants');
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'Product Name',
    'Product Slug',
    'Variant SKU',
    'Variant Name',
    'Price',
    'Sale Price',
    'Cost Price',
    'Stock Quantity',
    'Manage Stock (0 or 1)',
    'Stock Status',
    'Is Active (0 or 1)',
    'Attributes JSON',
    'Weight',
    'Length',
    'Width',
    'Height',
    'Diameter',
    'Discount Type',
    'Discount Value',
    'Discount Active',
    'Measurements JSON',
], $headerStyle));
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'Sample T-Shirt',
    'sample-t-shirt',
    'TSHIRT-RED-M',
    'Red - Medium',
    '29.99',
    '24.99',
    '15.00',
    '50',
    '1',
    'in_stock',
    '1',
    '{"color":"red","size":"M"}',
    '0.2',
    '',
    '',
    '',
    '',
    'percentage',
    '17',
    '1',
    '[]',
]));
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'Sample T-Shirt',
    '',
    'TSHIRT-RED-L',
    'Red - Large',
    '29.99',
    '24.99',
    '15.00',
    '30',
    '1',
    'in_stock',
    '1',
    '{"color":"red","size":"L"}',
    '0.2',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '[]',
]));
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'Sample T-Shirt',
    '',
    'TSHIRT-BLUE-M',
    'Blue - Medium',
    '29.99',
    '',
    '15.00',
    '25',
    '1',
    'in_stock',
    '1',
    '{"color":"blue","size":"M"}',
    '0.2',
    '',
    '',
    '',
    '',
    '',
    '',
    '',
    '[]',
]));

// Images sheet
$writer->addNewSheetAndMakeItCurrent();
$writer->getCurrentSheet()->setName('Images');
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'Product Name',
    'Product Slug',
    'Image Path or URL',
    'Is Primary (0 or 1)',
    'Sort Order',
    'Alt Text',
], $headerStyle));
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'Sample T-Shirt',
    'sample-t-shirt',
    'products/sample-t-shirt-front.jpg',
    '1',
    '0',
    'Sample T-Shirt Front View',
]));
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'Sample T-Shirt',
    '',
    'products/sample-t-shirt-back.jpg',
    '0',
    '1',
    'Sample T-Shirt Back View',
]));
$writer->addRow(WriterEntityFactory::createRowFromArray([
    'Sample T-Shirt',
    '',
    'products/sample-t-shirt-side.jpg',
    '0',
    '2',
    'Sample T-Shirt Side View',
]));

$writer->close();

fwrite(STDOUT, "Regenerated products-template.xlsx at {$filePath}\n");


