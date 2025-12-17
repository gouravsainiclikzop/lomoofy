# Product Import Guide

## Overview
This guide explains how to import products in bulk using the Excel/CSV import feature. The import structure matches the product creation form where **SKU, Price, and Stock are managed at the variant level** (not product level).

## File Structure

### For Excel Files (.xlsx)
Use **3 separate sheets**: `Products`, `Variants`, and `Images`

### For CSV Files (.csv)
Use a **single CSV file** with the Products sheet structure. Variants and Images must be imported separately or included in the Products sheet if using a single-row format.

---

## Sheet 1: Products

### Required Fields
- **Product Name** (required) - The main product name

### Optional Fields
- **SEO Slug** - Custom URL slug (auto-generated from name if not provided)
- **Status** - `published` or `hidden` (default: `hidden`)
- **Short Description** - Brief product summary
- **Description** - Full product description (supports HTML)
- **Brand Slugs (comma separated)** - Brand slugs separated by commas (e.g., `other, brand-name`)
- **Category Slugs (comma separated)** - Category slugs separated by commas (e.g., `clothing, mens-clothing`)
- **Tag List** - Tags separated by commas (e.g., `casual, cotton, comfortable`)
- **Featured** - `1` for featured, `0` for not featured (default: `0`)
- **Requires Shipping (0 or 1)** - `1` if product requires shipping, `0` if not (default: `1`)
- **Free Shipping (0 or 1)** - `1` for free shipping, `0` for paid shipping (default: `0`)
- **GST Type (0 or 1)** - `1` for inclusive of GST, `0` for exclusive of GST (default: `1`)
- **GST Percentage** - `0`, `3`, `5`, `12`, `18`, or `28` (default: `18`)
- **Meta Title** - SEO meta title
- **Meta Description** - SEO meta description
- **Meta Keywords** - SEO meta keywords

### Important Notes
- **Products do NOT have SKU, Price, or Stock** - these are variant-level only
- Product Name is used to link products with variants and images
- Brand and Category slugs must match existing slugs in your system

---

## Sheet 2: Variants

### Required Fields
- **Product Name** OR **Product Slug** (required) - Links to the product in Products sheet
- **Variant SKU** (required) - Unique SKU for this variant
- **Price** (required) - Regular price for this variant

### Optional Fields
- **Variant Name** - Name for this variant (e.g., "Red - Medium")
- **Sale Price** - Discounted price
- **Cost Price** - Cost price for inventory management
- **Stock Quantity** - Number of items in stock (default: `0`)
- **Manage Stock (0 or 1)** - `1` to manage stock, `0` to not manage (default: `1`)
- **Stock Status** - `in_stock`, `out_of_stock`, or `on_backorder` (default: `in_stock`)
- **Is Active (0 or 1)** - `1` for active, `0` for inactive (default: `1`)
- **Attributes JSON** - JSON object with variant attributes (e.g., `{"color":"red","size":"M"}`)
- **Weight** - Product weight
- **Length** - Product length
- **Width** - Product width
- **Height** - Product height
- **Diameter** - Product diameter
- **Discount Type** - `percentage` or `amount`
- **Discount Value** - Discount amount or percentage
- **Discount Active** - `1` for active discount, `0` for inactive
- **Measurements JSON** - JSON array with additional measurements

### Important Notes
- Each variant must have a **unique SKU** across all products
- If Variant SKU is empty, it will be auto-generated
- You can use either **Product Name** or **Product Slug** to link variants to products
- At least one variant is required per product

### Example Attributes JSON
```json
{"color":"red","size":"M"}
{"material":"cotton","pattern":"solid"}
{"storage":"128GB","color":"black"}
```

---

## Sheet 3: Images

### Required Fields
- **Product Name** OR **Product Slug** (required) - Links to the product in Products sheet
- **Image Path or URL** (required) - Path to image file or URL

### Optional Fields
- **Is Primary (0 or 1)** - `1` for primary image, `0` for secondary (default: `0`)
- **Sort Order** - Display order (lower numbers appear first, default: `0`)
- **Alt Text** - Alternative text for accessibility

### Important Notes
- You can use either **Product Name** or **Product Slug** to link images to products
- Image paths can be:
  - Relative paths from `storage/public` (e.g., `products/image.jpg`)
  - Full URLs (will be downloaded automatically)
- Multiple images per product are supported

---

## Example: Complete Import Structure

### Products Sheet
```
Product Name          | SEO Slug        | Status    | Short Description
Sample T-Shirt       | sample-t-shirt  | published | Comfortable cotton t-shirt
Sample Jeans         | sample-jeans    | published | Classic fit denim jeans
```

### Variants Sheet
```
Product Name    | Variant SKU    | Price  | Stock Quantity | Attributes JSON
Sample T-Shirt  | TSHIRT-RED-M   | 29.99  | 50             | {"color":"red","size":"M"}
Sample T-Shirt  | TSHIRT-RED-L   | 29.99  | 30             | {"color":"red","size":"L"}
Sample T-Shirt  | TSHIRT-BLUE-M  | 29.99  | 25             | {"color":"blue","size":"M"}
Sample Jeans    | JEANS-32-BLUE  | 49.99  | 20             | {"color":"blue","size":"32"}
```

### Images Sheet
```
Product Name   | Image Path                    | Is Primary | Sort Order
Sample T-Shirt | products/tshirt-front.jpg     | 1          | 0
Sample T-Shirt | products/tshirt-back.jpg      | 0          | 1
Sample Jeans   | products/jeans-front.jpg      | 1          | 0
```

---

## Common Issues and Solutions

### Issue: "Product Name is required"
**Solution:** Ensure the Products sheet has a `Product Name` column with values in every row.

### Issue: "Variants provided for product identifier 'X', but product not found"
**Solution:** Make sure the Product Name or Product Slug in the Variants sheet exactly matches the Product Name in the Products sheet (case-sensitive).

### Issue: "Variant SKU must be unique"
**Solution:** Each variant SKU must be unique across all products. Check for duplicate SKUs.

### Issue: "Brand/Category slug not found"
**Solution:** Ensure brand and category slugs exist in your system. Export existing brands/categories to see their slugs.

### Issue: Images not linking to products
**Solution:** Use the exact Product Name or Product Slug from the Products sheet in the Images sheet.

---

## Tips for Successful Import

1. **Start Small**: Test with 2-3 products first before importing large batches
2. **Check Slugs**: Verify brand and category slugs exist before importing
3. **Unique SKUs**: Ensure all variant SKUs are unique
4. **Required Variants**: Every product must have at least one variant
5. **Image Paths**: Use relative paths from `storage/public` or full URLs
6. **JSON Format**: When using Attributes JSON, ensure valid JSON format
7. **Backup First**: Always backup your database before bulk imports

---

## Download Template

The Excel template can be downloaded from the Import Products modal in the admin panel, or accessed directly at:
`/import-templates/products-template.xlsx`

---

## Support

If you encounter issues during import, check the import summary for detailed error messages. Each error will indicate the row number and specific issue to help you fix the data.

