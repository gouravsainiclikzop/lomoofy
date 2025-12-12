@extends('layouts.admin')

@section('title', 'View Product: ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="py-5">
        <!-- Header -->
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View Product</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">View Product</h1>
            </div>
            <div class="col-auto d-flex flex-wrap gap-2 justify-content-end">
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Products
                </a>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit Product
                </a>
            </div>
        </div>

        <!-- Product Details -->
        <div class="row g-4">
            <div class="col-12">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Product Name:</strong></div>
                            <div class="col-md-9">{{ $product->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Slug:</strong></div>
                            <div class="col-md-9"><code>{{ $product->slug }}</code></div>
                        </div>
                        @if($product->short_description)
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Short Description:</strong></div>
                            <div class="col-md-9">{!! $product->short_description !!}</div>
                        </div>
                        @endif
                        @if($product->description)
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Description:</strong></div>
                            <div class="col-md-9">{!! $product->description !!}</div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Status:</strong></div>
                            <div class="col-md-9">
                                <span class="badge bg-{{ $product->status === 'published' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Featured:</strong></div>
                            <div class="col-md-9">
                                <span class="badge bg-{{ $product->featured ? 'warning' : 'secondary' }}">
                                    {{ $product->featured ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>
                        @if($product->tags)
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Tags:</strong></div>
                            <div class="col-md-9">
                                @foreach(explode(',', $product->tags) as $tag)
                                    <span class="badge bg-info me-1">{{ trim($tag) }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @if($product->requires_shipping !== null)
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Requires Shipping:</strong></div>
                            <div class="col-md-9">
                                <span class="badge bg-{{ $product->requires_shipping ? 'success' : 'secondary' }}">
                                    {{ $product->requires_shipping ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($product->free_shipping !== null)
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Free Shipping:</strong></div>
                            <div class="col-md-9">
                                <span class="badge bg-{{ $product->free_shipping ? 'success' : 'secondary' }}">
                                    {{ $product->free_shipping ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>
                        @endif
                        @if($product->unit)
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Unit:</strong></div>
                            <div class="col-md-9">
                                {{ $product->unit->name }} ({{ $product->unit->symbol }})
                            </div>
                        </div>
                        @endif
                        
                        <!-- Product Images -->
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Product Images:</strong></div>
                            <div class="col-md-9">
                                @if($product->images->count() > 0)
                                    <div class="row g-2">
                                        @foreach($product->images as $image)
                                            <div class="col-auto">
                                                <div class="position-relative">
                                                    <img src="{{ asset('storage/' . $image->image_path) }}" 
                                                         alt="Product Image" 
                                                         class="img-thumbnail" 
                                                         style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                                    @if($image->is_primary)
                                                        <span class="badge bg-success position-absolute top-0 start-0 m-1">Primary</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">No images available.</span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Created:</strong></div>
                            <div class="col-md-9">
                                <div class="small text-muted">{{ $product->created_at->format('Y-m-d H:i:s') }}</div>
                            </div>
                        </div>
                        @if($product->updated_at)
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Last Updated:</strong></div>
                            <div class="col-md-9">
                                <div class="small text-muted">{{ $product->updated_at->format('Y-m-d H:i:s') }}</div>
                            </div>
                        </div>
                        @endif
                        @if($product->published_at)
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Published At:</strong></div>
                            <div class="col-md-9">
                                <div class="small text-muted">{{ $product->published_at->format('Y-m-d H:i:s') }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Categories & Brands -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Categories & Brands</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Categories:</strong></div>
                            <div class="col-md-9">
                                @if($product->categories->count() > 0)
                                    @foreach($product->categories as $category)
                                        @php
                                            $path = [];
                                            $current = $category;
                                            while ($current) {
                                                array_unshift($path, $current->name);
                                                $current = $current->parent;
                                            }
                                        @endphp
                                        <span class="badge bg-{{ $category->pivot->is_primary ?? false ? 'success' : 'secondary' }} me-1">
                                            {{ implode(' > ', $path) }}
                                        </span>
                                    @endforeach
                                @elseif($product->category)
                                    <span class="badge bg-success">{{ $product->category_path }}</span>
                                @else
                                    <span class="text-muted">No categories</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Brands:</strong></div>
                            <div class="col-md-9">
                                @if($product->brands->count() > 0)
                                    @foreach($product->brands as $brand)
                                        <span class="badge bg-{{ $brand->pivot->is_primary ?? false ? 'primary' : 'secondary' }} me-1">
                                            {{ $brand->name }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No brands</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variants -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Product Variants ({{ $product->variants->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($product->variants->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>SKU</th>
                                            <th>Name</th>
                                            <th>Attributes</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                            <th width="100">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($product->variants as $index => $variant)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><code>{{ $variant->sku }}</code></td>
                                                <td>{{ $variant->name }}</td>
                                                <td>
                                                    @if($variant->attributes && count($variant->attributes) > 0)
                                                        @foreach($variant->attributes as $attrId => $attrValue)
                                                            <span class="badge bg-secondary me-1">{{ $attrValue }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>₹{{ number_format($variant->price ?? 0, 2) }}</strong>
                                                    @if($variant->sale_price)
                                                            <br><span class="text-danger">₹{{ number_format($variant->sale_price, 2) }}</span>
                                                        @if($variant->price && $variant->price > 0)
                                                                <small class="text-danger d-block">
                                                                {{ round((($variant->price - $variant->sale_price) / $variant->price) * 100) }}% OFF
                                                            </small>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $variant->stock_status === 'in_stock' ? 'success' : ($variant->stock_status === 'out_of_stock' ? 'danger' : 'warning') }}">
                                                        {{ $variant->stock_quantity ?? 0 }}
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $variant->stock_status ?? 'in_stock')) }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $variant->is_active ? 'success' : 'secondary' }}">
                                                        {{ $variant->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary view-variant-details" 
                                                            data-variant-id="{{ $variant->id }}"
                                                            title="View Details">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No variants found for this product.</p>
                        @endif
                    </div>
                </div>

                <!-- SEO Information -->
                @if($product->meta_title || $product->meta_description || $product->meta_keywords || $product->metadata || $product->json_ld)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>SEO Information</h5>
                    </div>
                    <div class="card-body">
                        @if($product->meta_title)
                        <div class="mb-3">
                            <strong>Meta Title:</strong>
                            <p class="mb-0 small">{{ $product->meta_title }}</p>
                        </div>
                        @endif
                        @if($product->meta_description)
                        <div class="mb-3">
                            <strong>Meta Description:</strong>
                            <p class="mb-0 small">{{ $product->meta_description }}</p>
                        </div>
                        @endif
                        @if($product->meta_keywords)
                        <div class="mb-3">
                            <strong>Meta Keywords:</strong>
                            <p class="mb-0 small">{{ $product->meta_keywords }}</p>
                        </div>
                        @endif
                        @if($product->metadata)
                        <div class="mb-3">
                            <strong>Raw Metadata:</strong>
                            <pre class="small bg-light p-2 rounded" style="max-height: 200px; overflow-y: auto;"><code>{{ $product->metadata }}</code></pre>
                        </div>
                        @endif
                        @if($product->json_ld)
                        <div class="mb-3">
                            <strong>JSON-LD:</strong>
                            <pre class="small bg-light p-2 rounded" style="max-height: 200px; overflow-y: auto;"><code>{{ json_encode($product->json_ld, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Variant Details Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="variantDetailsOffcanvas" aria-labelledby="variantDetailsOffcanvasLabel" style="width: 600px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="variantDetailsOffcanvasLabel">
            <i class="fas fa-box me-2"></i>Variant Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="variantDetailsContent">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @php
        $variantDataArray = $product->variants->map(function($variant) {
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'name' => $variant->name,
                'barcode' => $variant->barcode,
                'attributes' => $variant->attributes,
                'price' => (float)($variant->price ?? 0),
                'sale_price' => $variant->sale_price ? (float)$variant->sale_price : null,
                'cost_price' => $variant->cost_price ? (float)$variant->cost_price : null,
                'stock_quantity' => (int)($variant->stock_quantity ?? 0),
                'stock_status' => $variant->stock_status,
                'manage_stock' => (bool)$variant->manage_stock,
                'low_stock_threshold' => (int)($variant->low_stock_threshold ?? 0),
                'is_active' => (bool)$variant->is_active,
                'sort_order' => (int)($variant->sort_order ?? 0),
                'discount_type' => $variant->discount_type,
                'discount_value' => $variant->discount_value ? (float)$variant->discount_value : null,
                'discount_active' => $variant->discount_active !== null ? (bool)$variant->discount_active : null,
                'sale_price_start' => $variant->sale_price_start ? $variant->sale_price_start->format('Y-m-d H:i:s') : null,
                'sale_price_end' => $variant->sale_price_end ? $variant->sale_price_end->format('Y-m-d H:i:s') : null,
                'weight' => $variant->weight ? (float)$variant->weight : null,
                'length' => $variant->length ? (float)$variant->length : null,
                'width' => $variant->width ? (float)$variant->width : null,
                'height' => $variant->height ? (float)$variant->height : null,
                'diameter' => $variant->diameter ? (float)$variant->diameter : null,
                'measurements' => $variant->measurements ?? [],
                'highlights_details' => $variant->highlights_details ?? [],
                'images' => $variant->images->map(function($img) {
                    return [
                        'id' => $img->id,
                        'image_path' => asset('storage/' . $img->image_path),
                        'is_primary' => (bool)($img->is_primary ?? false)
                    ];
                })->values()->toArray()
            ];
        })->values()->toArray();
    @endphp
    const variantData = @json($variantDataArray);

    // View variant details button click
    $(document).on('click', '.view-variant-details', function() {
        const variantId = $(this).data('variant-id');
        const variant = variantData.find(v => v.id == variantId);
        
        if (!variant) {
            alert('Variant not found');
                    return;
        }

        // Build variant details HTML
        let html = '<div class="variant-details">';
        
        // Basic Information
        html += '<div class="mb-4">';
        html += '<h6 class="border-bottom pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>';
        html += '<div class="row g-3 mb-2">';
        html += '<div class="col-4"><strong>SKU:</strong></div><div class="col-8"><code>' + variant.sku + '</code></div>';
        html += '<div class="col-4"><strong>Name:</strong></div><div class="col-8">' + (variant.name || '—') + '</div>';
        if (variant.barcode) {
            html += '<div class="col-4"><strong>Barcode:</strong></div><div class="col-8"><code>' + variant.barcode + '</code></div>';
        }
        html += '<div class="col-4"><strong>Status:</strong></div><div class="col-8">';
        html += '<span class="badge bg-' + (variant.is_active ? 'success' : 'secondary') + '">' + (variant.is_active ? 'Active' : 'Inactive') + '</span>';
        html += '</div>';
        html += '<div class="col-4"><strong>Sort Order:</strong></div><div class="col-8">' + (variant.sort_order || 0) + '</div>';
        html += '</div>';
        
        // Attributes
        if (variant.attributes && Object.keys(variant.attributes).length > 0) {
            html += '<div class="mt-3"><strong>Attributes:</strong><br>';
            for (let attrId in variant.attributes) {
                html += '<span class="badge bg-secondary me-1">' + variant.attributes[attrId] + '</span>';
            }
            html += '</div>';
        }
        html += '</div>';

        // Pricing Information
        html += '<div class="mb-4">';
        html += '<h6 class="border-bottom pb-2 mb-3"><i class="fas fa-tag me-2"></i>Pricing Information</h6>';
        html += '<div class="row g-3 mb-2">';
        html += '<div class="col-4"><strong>Price:</strong></div><div class="col-8"><strong>₹' + parseFloat(variant.price || 0).toFixed(2) + '</strong></div>';
        if (variant.sale_price) {
            html += '<div class="col-4"><strong>Sale Price:</strong></div><div class="col-8"><span class="text-danger">₹' + parseFloat(variant.sale_price).toFixed(2) + '</span>';
            if (variant.price && variant.price > 0) {
                const discount = Math.round(((variant.price - variant.sale_price) / variant.price) * 100);
                html += ' <span class="badge bg-danger">' + discount + '% OFF</span>';
            }
            html += '</div>';
        }
        if (variant.cost_price) {
            html += '<div class="col-4"><strong>Cost Price:</strong></div><div class="col-8">₹' + parseFloat(variant.cost_price).toFixed(2) + '</div>';
        }
        html += '</div>';
        
        // Discount Information
        if (variant.discount_type || variant.discount_value !== null || variant.discount_active !== null || variant.sale_price_start || variant.sale_price_end) {
            html += '<div class="mt-3"><strong>Discount Details:</strong><br>';
            if (variant.discount_type) {
                html += '<div class="mb-1"><strong>Type:</strong> ' + variant.discount_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + '</div>';
            }
            if (variant.discount_value !== null) {
                html += '<div class="mb-1"><strong>Value:</strong> ₹' + parseFloat(variant.discount_value).toFixed(2) + '</div>';
            }
            if (variant.discount_active !== null) {
                html += '<div class="mb-1"><strong>Active:</strong> <span class="badge bg-' + (variant.discount_active ? 'success' : 'secondary') + '">' + (variant.discount_active ? 'Yes' : 'No') + '</span></div>';
            }
            if (variant.sale_price_start) {
                html += '<div class="mb-1"><strong>Sale Start:</strong> ' + variant.sale_price_start + '</div>';
            }
            if (variant.sale_price_end) {
                html += '<div class="mb-1"><strong>Sale End:</strong> ' + variant.sale_price_end + '</div>';
            }
            html += '</div>';
        }
        html += '</div>';

        // Inventory Information
        html += '<div class="mb-4">';
        html += '<h6 class="border-bottom pb-2 mb-3"><i class="fas fa-warehouse me-2"></i>Inventory Information</h6>';
        html += '<div class="row g-3 mb-2">';
        html += '<div class="col-4"><strong>Stock Quantity:</strong></div><div class="col-8">';
        html += '<span class="badge bg-' + (variant.stock_status === 'in_stock' ? 'success' : (variant.stock_status === 'out_of_stock' ? 'danger' : 'warning')) + '">' + (variant.stock_quantity || 0) + '</span>';
        html += '</div>';
        html += '<div class="col-4"><strong>Stock Status:</strong></div><div class="col-8">';
        html += '<span class="badge bg-' + (variant.stock_status === 'in_stock' ? 'success' : (variant.stock_status === 'out_of_stock' ? 'danger' : 'warning')) + '">';
        html += (variant.stock_status || 'in_stock').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        html += '</span></div>';
        html += '<div class="col-4"><strong>Manage Stock:</strong></div><div class="col-8">';
        html += '<span class="badge bg-' + (variant.manage_stock ? 'info' : 'secondary') + '">' + (variant.manage_stock ? 'Yes' : 'No') + '</span>';
        html += '</div>';
        if (variant.low_stock_threshold) {
            html += '<div class="col-4"><strong>Low Stock Threshold:</strong></div><div class="col-8">' + variant.low_stock_threshold + '</div>';
        }
        html += '</div>';
        html += '</div>';

        // Dimensions
        if (variant.weight || variant.length || variant.width || variant.height || variant.diameter) {
            html += '<div class="mb-4">';
            html += '<h6 class="border-bottom pb-2 mb-3"><i class="fas fa-ruler me-2"></i>Dimensions</h6>';
            html += '<div class="row g-3 mb-2">';
            if (variant.weight) {
                html += '<div class="col-4"><strong>Weight:</strong></div><div class="col-8">' + parseFloat(variant.weight).toFixed(2) + ' kg</div>';
            }
            if (variant.length) {
                html += '<div class="col-4"><strong>Length:</strong></div><div class="col-8">' + parseFloat(variant.length).toFixed(2) + ' cm</div>';
            }
            if (variant.width) {
                html += '<div class="col-4"><strong>Width:</strong></div><div class="col-8">' + parseFloat(variant.width).toFixed(2) + ' cm</div>';
            }
            if (variant.height) {
                html += '<div class="col-4"><strong>Height:</strong></div><div class="col-8">' + parseFloat(variant.height).toFixed(2) + ' cm</div>';
            }
            if (variant.diameter) {
                html += '<div class="col-4"><strong>Diameter:</strong></div><div class="col-8">' + parseFloat(variant.diameter).toFixed(2) + ' cm</div>';
            }
            html += '</div>';
            html += '</div>';
        }

        // Measurements
        if (variant.measurements && variant.measurements.length > 0) {
            html += '<div class="mb-4">';
            html += '<h6 class="border-bottom pb-2 mb-3"><i class="fas fa-ruler-combined me-2"></i>Measurements</h6>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-bordered">';
            html += '<thead><tr><th>Attribute</th><th>Value</th><th>Unit</th></tr></thead>';
            html += '<tbody>';
            variant.measurements.forEach(function(measurement) {
                html += '<tr>';
                html += '<td>' + (measurement.attribute_name || 'Measurement') + '</td>';
                html += '<td>' + (measurement.value || '') + '</td>';
                html += '<td>' + (measurement.unit_symbol || measurement.unit_name || '') + '</td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
            html += '</div>';
            html += '</div>';
        }

        // Highlights & Details
        if (variant.highlights_details && variant.highlights_details.length > 0) {
            html += '<div class="mb-4">';
            html += '<h6 class="border-bottom pb-2 mb-3"><i class="fas fa-star me-2 text-warning"></i>Highlights & Details</h6>';
            variant.highlights_details.forEach(function(highlight) {
                html += '<div class="mb-3 p-3 bg-light rounded border">';
                html += '<h6 class="mb-2">' + (highlight.heading_name || '') + '</h6>';
                if (highlight.bullet_points && highlight.bullet_points.length > 0) {
                    html += '<ul class="mb-0">';
                    highlight.bullet_points.forEach(function(point) {
                        html += '<li>' + point + '</li>';
                    });
                    html += '</ul>';
                }
                html += '</div>';
            });
            html += '</div>';
        }

        // Variant Images
        if (variant.images && variant.images.length > 0) {
            html += '<div class="mb-4">';
            html += '<h6 class="border-bottom pb-2 mb-3"><i class="fas fa-images me-2"></i>Variant Images (' + variant.images.length + ')</h6>';
            html += '<div class="d-flex flex-wrap gap-2">';
            variant.images.forEach(function(image) {
                html += '<div class="position-relative">';
                html += '<img src="' + image.image_path + '" alt="Variant Image" class="img-thumbnail" style="max-width: 150px; max-height: 150px; object-fit: cover;">';
                if (image.is_primary) {
                    html += '<span class="badge bg-success position-absolute top-0 start-0 m-1">Primary</span>';
                }
                html += '</div>';
            });
            html += '</div>';
            html += '</div>';
        }

        html += '</div>';

        // Update offcanvas content and show
        $('#variantDetailsContent').html(html);
        const offcanvas = new bootstrap.Offcanvas(document.getElementById('variantDetailsOffcanvas'));
        offcanvas.show();
    });
});
</script>
@endpush
@endsection

