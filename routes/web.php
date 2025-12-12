<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\FrontendController;

// Frontend Routes
//assets for this are located in "public/frontend/"
Route::get('/', [FrontendController::class, 'index'])->name('frontend.index');
Route::get('/shop', [FrontendController::class, 'shop'])->name('frontend.shop');
Route::get('/product', [FrontendController::class, 'product'])->name('frontend.product');
Route::get('/about-us', [FrontendController::class, 'aboutUs'])->name('frontend.about-us');
Route::get('/contact', [FrontendController::class, 'contact'])->name('frontend.contact');
Route::get('/privacy', [FrontendController::class, 'privacy'])->name('frontend.privacy');
Route::get('/faq', [FrontendController::class, 'faq'])->name('frontend.faq');
Route::get('/my-orders', [FrontendController::class, 'myOrders'])->name('frontend.my-orders');
Route::get('/wishlist', [FrontendController::class, 'wishlist'])->name('frontend.wishlist');
Route::get('/profile-info', [FrontendController::class, 'profileInfo'])->name('frontend.profile-info');
Route::get('/addresses', [FrontendController::class, 'addresses'])->name('frontend.addresses');
Route::get('/payment-methode', [FrontendController::class, 'paymentMethode'])->name('frontend.payment-methode');
Route::get('/shoping-cart', [FrontendController::class, 'shopingCart'])->name('frontend.shoping-cart');
Route::get('/checkout', [FrontendController::class, 'checkout'])->name('frontend.checkout');
Route::get('/complete-order', [FrontendController::class, 'completeOrder'])->name('frontend.complete-order'); 
 

Route::get('/admin', [AuthController::class, 'showLogin'])->name('admin.login'); 
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post'); 

// Protected Dashboard Routes (require authentication)
Route::middleware(['auth', 'refreshStorage'])->group(function () {  
    // Public Admin Auth Routes
    Route::any('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
    // Dashboard 
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/orders', [DashboardController::class, 'getRecentOrders'])->name('dashboard.orders');
    Route::get('/dashboard/sales-chart', [DashboardController::class, 'getSalesChart'])->name('dashboard.sales-chart');
    Route::get('/dashboard/orders-by-status', [DashboardController::class, 'getOrdersByStatus'])->name('dashboard.orders-by-status');
    Route::get('/dashboard/top-products', [DashboardController::class, 'getTopProducts'])->name('dashboard.top-products');
    
    // Brands (GET and POST only)
    Route::get('/brands', [\App\Http\Controllers\BrandController::class, 'index'])->name('brands.index');
    Route::get('/brands/data', [\App\Http\Controllers\BrandController::class, 'getData'])->name('brands.data');
    Route::get('/brands/{id}/edit', [\App\Http\Controllers\BrandController::class, 'edit'])->name('brands.edit');
    Route::post('/brands', [\App\Http\Controllers\BrandController::class, 'store'])->name('brands.store');
    Route::post('/brands/bulk-delete', [\App\Http\Controllers\BrandController::class, 'bulkDelete'])->name('brands.bulk-delete'); // Must be before /brands/{id}
    Route::post('/brands/{id}', [\App\Http\Controllers\BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{id}', [\App\Http\Controllers\BrandController::class, 'destroy'])->name('brands.destroy');
    
    // Categories (GET and POST only)
    Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/data', [\App\Http\Controllers\CategoryController::class, 'getData'])->name('categories.data');
    Route::get('/categories/parents', [\App\Http\Controllers\CategoryController::class, 'getParents'])->name('categories.parents');
    Route::get('/categories/attributes', [\App\Http\Controllers\CategoryController::class, 'getAvailableAttributes'])->name('categories.attributes');
    Route::get('/categories/edit', [\App\Http\Controllers\CategoryController::class, 'edit'])->name('categories.edit');
    Route::post('/categories/store', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/update', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
    Route::post('/categories/delete', [\App\Http\Controllers\CategoryController::class, 'delete'])->name('categories.delete');
    Route::post('/categories/restore', [\App\Http\Controllers\CategoryController::class, 'restore'])->name('categories.restore');
    Route::post('/categories/bulk-delete', [\App\Http\Controllers\CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
    Route::post('/categories/update-status', [\App\Http\Controllers\CategoryController::class, 'updateStatus'])->name('categories.updateStatus');
    Route::post('/categories/update-parent', [\App\Http\Controllers\CategoryController::class, 'updateParent'])->name('categories.updateParent');
    
    // Profile (GET and POST only)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update-image', [\App\Http\Controllers\ProfileController::class, 'updateImage'])->name('profile.updateImage');
    Route::post('/profile/update-name', [\App\Http\Controllers\ProfileController::class, 'updateName'])->name('profile.updateName');
    Route::post('/profile/update-email', [\App\Http\Controllers\ProfileController::class, 'updateEmail'])->name('profile.updateEmail');
    Route::post('/profile/update-password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    
    // Roles (GET and POST only)
    Route::get('/roles', [\App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/data', [\App\Http\Controllers\RoleController::class, 'getData'])->name('roles.data');
    Route::get('/roles/edit', [\App\Http\Controllers\RoleController::class, 'edit'])->name('roles.edit');
    Route::get('/roles/permissions', [\App\Http\Controllers\RoleController::class, 'getPermissions'])->name('roles.permissions');
    Route::post('/roles/store', [\App\Http\Controllers\RoleController::class, 'store'])->name('roles.store');
    Route::post('/roles/update', [\App\Http\Controllers\RoleController::class, 'update'])->name('roles.update');
    Route::post('/roles/delete', [\App\Http\Controllers\RoleController::class, 'delete'])->name('roles.delete');
    Route::post('/roles/assign-users', [\App\Http\Controllers\RoleController::class, 'assignUsers'])->name('roles.assignUsers');
    
    
    // Users (GET and POST only)x
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/users/data', [\App\Http\Controllers\UserController::class, 'getData'])->name('users.data');
    Route::get('/users/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
    Route::get('/users/roles', [\App\Http\Controllers\UserController::class, 'getRoles'])->name('users.roles');
    Route::post('/users/store', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::post('/users/update', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::post('/users/delete', [\App\Http\Controllers\UserController::class, 'delete'])->name('users.delete');
    
    // Sections & Pages (GET and POST only)
    Route::get('/sections', [\App\Http\Controllers\SectionController::class, 'index'])->name('sections.index');
    
    // Pages
    Route::get('/sections/pages', [\App\Http\Controllers\SectionController::class, 'getPages'])->name('sections.pages');
    Route::get('/sections/pages/edit', [\App\Http\Controllers\SectionController::class, 'editPage'])->name('sections.pages.edit');
    Route::post('/sections/pages/store', [\App\Http\Controllers\SectionController::class, 'storePage'])->name('sections.pages.store');
    Route::post('/sections/pages/update', [\App\Http\Controllers\SectionController::class, 'updatePage'])->name('sections.pages.update');
    Route::post('/sections/pages/delete', [\App\Http\Controllers\SectionController::class, 'deletePage'])->name('sections.pages.delete');
    Route::post('/sections/pages/update-sort-order', [\App\Http\Controllers\SectionController::class, 'updatePagesSortOrder'])->name('sections.pages.updateSortOrder');
        
    // Sections
    Route::get('/sections/get', [\App\Http\Controllers\SectionController::class, 'getSections'])->name('sections.get');
    Route::get('/sections/home', [\App\Http\Controllers\SectionController::class, 'getHomePageSections'])->name('sections.home');
    Route::get('/sections/page', [\App\Http\Controllers\SectionController::class, 'getPageSections'])->name('sections.page');
    Route::get('/sections/edit', [\App\Http\Controllers\SectionController::class, 'edit'])->name('sections.edit');
    Route::post('/sections/store', [\App\Http\Controllers\SectionController::class, 'store'])->name('sections.store');
    Route::post('/sections/update', [\App\Http\Controllers\SectionController::class, 'update'])->name('sections.update');
    Route::post('/sections/delete', [\App\Http\Controllers\SectionController::class, 'delete'])->name('sections.delete');
    Route::post('/sections/toggle-variant', [\App\Http\Controllers\SectionController::class, 'toggleVariant'])->name('sections.toggleVariant');
    Route::post('/sections/update-variant-image', [\App\Http\Controllers\SectionController::class, 'updateVariantImage'])->name('sections.updateVariantImage');
    Route::post('/sections/update-sort-order', [\App\Http\Controllers\SectionController::class, 'updateSortOrder'])->name('sections.updateSortOrder');
    Route::post('/sections/initialize-home', [\App\Http\Controllers\SectionController::class, 'initializeHomePageSections'])->name('sections.initializeHome');
    
    // Products
    Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [\App\Http\Controllers\ProductController::class, 'create'])->name('products.create');
    Route::get('/products/quick-create', [\App\Http\Controllers\ProductController::class, 'quickCreate'])->name('products.quick-create');
    Route::post('/products/quick-store', [\App\Http\Controllers\ProductController::class, 'quickStore'])->name('products.quick-store');
    Route::post('/products/import', [ProductImportController::class, 'store'])->name('products.import');
    
    // Export routes
    Route::get('/exports/brands', [\App\Http\Controllers\ExportController::class, 'exportBrands'])->name('exports.brands');
    Route::get('/exports/categories', [\App\Http\Controllers\ExportController::class, 'exportCategories'])->name('exports.categories');
    Route::get('/exports/products', [\App\Http\Controllers\ExportController::class, 'exportProducts'])->name('exports.products');
    Route::get('/exports/variants', [\App\Http\Controllers\ExportController::class, 'exportVariants'])->name('exports.variants');
    Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/data', [\App\Http\Controllers\ProductController::class, 'getData'])->name('products.data');
    Route::get('/products/attributes', [\App\Http\Controllers\ProductController::class, 'getAttributes'])->name('products.attributes');
    Route::get('/products/attributes-by-category', [\App\Http\Controllers\ProductController::class, 'getAttributesByCategory'])->name('products.attributes-by-category');
    Route::get('/products/categories-by-brand', [\App\Http\Controllers\ProductController::class, 'getCategoriesByBrand'])->name('products.categories-by-brand');
    Route::get('/products/units-by-type', [\App\Http\Controllers\ProductController::class, 'getUnitsByType'])->name('products.units-by-type');
    Route::get('/products/search', [\App\Http\Controllers\ProductController::class, 'search'])->name('products.search');
    Route::get('/products/{product}', [\App\Http\Controllers\ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [\App\Http\Controllers\ProductController::class, 'edit'])->name('products.edit');
    Route::post('/products/{product}/update', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
    Route::post('/products/{product}/delete', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/bulk-delete', [\App\Http\Controllers\ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::post('/products/{product}/toggle-status', [\App\Http\Controllers\ProductController::class, 'toggleStatus'])->name('products.toggleStatus');
    Route::post('/products/{product}/toggle-featured', [\App\Http\Controllers\ProductController::class, 'toggleFeatured'])->name('products.toggleFeatured');
    Route::post('/products/{product}/generate-variants', [\App\Http\Controllers\ProductController::class, 'generateVariants'])->name('products.generateVariants');
    Route::get('/products/{product}/seo', [\App\Http\Controllers\ProductController::class, 'getSeo'])->name('products.seo.get');
    Route::put('/products/{product}/seo', [\App\Http\Controllers\ProductController::class, 'updateSeo'])->name('products.seo.update');
    
    // Variant Heading Suggestions API
    Route::get('/variant-headings/suggestions', [\App\Http\Controllers\ProductController::class, 'getHeadingSuggestions'])->name('variant-headings.suggestions');
    Route::post('/variant-headings/save-suggestion', [\App\Http\Controllers\ProductController::class, 'saveHeadingSuggestion'])->name('variant-headings.save-suggestion');
    
    // Inventory Management
    Route::get('/inventory', [\App\Http\Controllers\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/data', [\App\Http\Controllers\InventoryController::class, 'getData'])->name('inventory.data');
    Route::get('/inventory/sample', [\App\Http\Controllers\InventoryController::class, 'downloadSample'])->name('inventory.sample');
    Route::get('/inventory/warehouses', [\App\Http\Controllers\InventoryController::class, 'getWarehouses'])->name('inventory.warehouses');
    Route::get('/inventory/warehouses/{warehouseId}/locations', [\App\Http\Controllers\InventoryController::class, 'getWarehouseLocations'])->name('inventory.warehouse-locations');
    Route::get('/inventory/{variantId}/stock-breakdown', [\App\Http\Controllers\InventoryController::class, 'getStockBreakdown'])->name('inventory.stock-breakdown');
    Route::post('/inventory/bulk-add', [\App\Http\Controllers\InventoryController::class, 'bulkAddStock'])->name('inventory.bulk-add');
    Route::post('/inventory/import', [\App\Http\Controllers\InventoryController::class, 'import'])->name('inventory.import');
    Route::post('/inventory/{id}', [\App\Http\Controllers\InventoryController::class, 'update'])->name('inventory.update');
    
    // Lead Masters Management (under Master Data)
    Route::get('/lead-masters', [\App\Http\Controllers\LeadMasterController::class, 'index'])->name('lead-masters.index');
    Route::get('/lead-masters/data', [\App\Http\Controllers\LeadMasterController::class, 'getData'])->name('lead-masters.data');
    Route::post('/lead-masters/status', [\App\Http\Controllers\LeadMasterController::class, 'storeStatus'])->name('lead-masters.store-status');
    Route::post('/lead-masters/status/{id}', [\App\Http\Controllers\LeadMasterController::class, 'updateStatus'])->name('lead-masters.update-status');
    Route::post('/lead-masters/status/{id}/delete', [\App\Http\Controllers\LeadMasterController::class, 'deleteStatus'])->name('lead-masters.delete-status');
    Route::post('/lead-masters/source', [\App\Http\Controllers\LeadMasterController::class, 'storeSource'])->name('lead-masters.store-source');
    Route::post('/lead-masters/source/{id}', [\App\Http\Controllers\LeadMasterController::class, 'updateSource'])->name('lead-masters.update-source');
    Route::post('/lead-masters/source/{id}/delete', [\App\Http\Controllers\LeadMasterController::class, 'deleteSource'])->name('lead-masters.delete-source');
    Route::post('/lead-masters/priority', [\App\Http\Controllers\LeadMasterController::class, 'storePriority'])->name('lead-masters.store-priority');
    Route::post('/lead-masters/priority/{id}', [\App\Http\Controllers\LeadMasterController::class, 'updatePriority'])->name('lead-masters.update-priority');
    Route::post('/lead-masters/priority/{id}/delete', [\App\Http\Controllers\LeadMasterController::class, 'deletePriority'])->name('lead-masters.delete-priority');
    Route::post('/lead-masters/tag', [\App\Http\Controllers\LeadMasterController::class, 'storeTag'])->name('lead-masters.store-tag');
    Route::post('/lead-masters/tag/{id}', [\App\Http\Controllers\LeadMasterController::class, 'updateTag'])->name('lead-masters.update-tag');
    Route::post('/lead-masters/tag/{id}/delete', [\App\Http\Controllers\LeadMasterController::class, 'deleteTag'])->name('lead-masters.delete-tag');
    
    // Lead Management
    Route::get('/leads', [\App\Http\Controllers\LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/master-data', [\App\Http\Controllers\LeadController::class, 'getMasterData'])->name('leads.master-data');
    Route::post('/leads', [\App\Http\Controllers\LeadController::class, 'store'])->name('leads.store');
    Route::get('/leads/{id}', [\App\Http\Controllers\LeadController::class, 'show'])->name('leads.show');
    Route::post('/leads/{id}', [\App\Http\Controllers\LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{id}', [\App\Http\Controllers\LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('/leads/{id}/status', [\App\Http\Controllers\LeadController::class, 'updateStatus'])->name('leads.update-status');
    Route::post('/leads/{id}/assign', [\App\Http\Controllers\LeadController::class, 'assign'])->name('leads.assign');
    Route::post('/leads/{id}/priority', [\App\Http\Controllers\LeadController::class, 'updatePriority'])->name('leads.update-priority');
    Route::get('/leads/{id}/activities', [\App\Http\Controllers\LeadController::class, 'getActivities'])->name('leads.activities');
    Route::post('/leads/{id}/activities', [\App\Http\Controllers\LeadController::class, 'storeActivity'])->name('leads.store-activity');
    Route::post('/leads/{id}/followup', [\App\Http\Controllers\LeadController::class, 'storeFollowup'])->name('leads.followup');
    Route::post('/leads/bulk-delete', [\App\Http\Controllers\LeadController::class, 'bulkDelete'])->name('leads.bulk-delete');
        
    // Master Data Management
    Route::get('/master-data/all', [\App\Http\Controllers\MasterDataController::class, 'getAll'])->name('master-data.all');
    Route::get('/master-data/export', [\App\Http\Controllers\MasterDataController::class, 'export'])->name('master-data.export');
    Route::post('/master-data/import', [\App\Http\Controllers\MasterDataController::class, 'import'])->name('master-data.import');
    
    // Attributes Management
    Route::get('/attributes', [\App\Http\Controllers\AttributeController::class, 'index'])->name('attributes.index');
    Route::get('/attributes/create', [\App\Http\Controllers\AttributeController::class, 'create'])->name('attributes.create');
    Route::post('/attributes', [\App\Http\Controllers\AttributeController::class, 'store'])->name('attributes.store');
    // Specific routes must be defined BEFORE parameterized routes to avoid conflicts
    Route::get('/attributes/numeric', [\App\Http\Controllers\AttributeController::class, 'getNumericAttributes'])->name('attributes.numeric');
    Route::get('/attributes/{attribute}', [\App\Http\Controllers\AttributeController::class, 'show'])->name('attributes.show');
    Route::get('/attributes/{attribute}/edit', [\App\Http\Controllers\AttributeController::class, 'edit'])->name('attributes.edit');
    Route::post('/attributes/{attribute}/update', [\App\Http\Controllers\AttributeController::class, 'update'])->name('attributes.update');
    Route::post('/attributes/{attribute}/delete', [\App\Http\Controllers\AttributeController::class, 'destroy'])->name('attributes.destroy');
    Route::get('/attributes/{attribute}/values', [\App\Http\Controllers\AttributeController::class, 'getValues'])->name('attributes.values');
    Route::post('/attributes/{attribute}/values', [\App\Http\Controllers\AttributeController::class, 'storeValue'])->name('attributes.store-value');
    Route::post('/attributes/values/{value}/update', [\App\Http\Controllers\AttributeController::class, 'updateValue'])->name('attributes.update-value');
    Route::post('/attributes/values/{value}/delete', [\App\Http\Controllers\AttributeController::class, 'destroyValue'])->name('attributes.destroy-value');
    Route::post('/attributes/update-sort-order', [\App\Http\Controllers\AttributeController::class, 'updateSortOrder'])->name('attributes.update-sort-order');
    Route::post('/attributes/bulk-delete', [\App\Http\Controllers\AttributeController::class, 'bulkDelete'])->name('attributes.bulk-delete');
    
    // Units Management Routes
    Route::resource('units', \App\Http\Controllers\UnitController::class);
    Route::get('/units-by-type', [\App\Http\Controllers\UnitController::class, 'getByType'])->name('units.by-type');
    Route::post('/units/{unit}/toggle-status', [\App\Http\Controllers\UnitController::class, 'toggleStatus'])->name('units.toggle-status');
    Route::post('/units/bulk-delete', [\App\Http\Controllers\UnitController::class, 'bulkDelete'])->name('units.bulk-delete');
    
    // Warehouses Management Routes (Master Data)
    Route::prefix('master-data/warehouses')->name('warehouses.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WarehouseController::class, 'index'])->name('index');
        Route::get('/data', [\App\Http\Controllers\WarehouseController::class, 'getData'])->name('data');
        Route::post('/', [\App\Http\Controllers\WarehouseController::class, 'store'])->name('store');
        Route::post('/bulk-delete', [\App\Http\Controllers\WarehouseController::class, 'bulkDelete'])->name('bulk-delete');
        
        // Warehouse Locations Routes (must come before parameterized routes)
        Route::prefix('locations')->name('locations.')->group(function () {
            Route::get('/data', [\App\Http\Controllers\WarehouseLocationController::class, 'getData'])->name('data');
            Route::get('/{id}/edit', [\App\Http\Controllers\WarehouseLocationController::class, 'edit'])->name('edit');
            Route::post('/', [\App\Http\Controllers\WarehouseLocationController::class, 'store'])->name('store');
            Route::post('/bulk-delete', [\App\Http\Controllers\WarehouseLocationController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/{id}', [\App\Http\Controllers\WarehouseLocationController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\WarehouseLocationController::class, 'destroy'])->name('destroy');
        });
        
        // Parameterized warehouse routes (must come after locations routes)
        Route::get('/{id}/edit', [\App\Http\Controllers\WarehouseController::class, 'edit'])->name('edit');
        Route::post('/{id}', [\App\Http\Controllers\WarehouseController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\WarehouseController::class, 'destroy'])->name('destroy');
    });
    
    // Shipping Management Routes (Master Data)
    Route::prefix('master-data/shipping')->name('shipping.')->group(function () {
        // Shipping Zones
        Route::prefix('zones')->name('zones.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ShippingZoneController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\ShippingZoneController::class, 'getData'])->name('data');
            Route::get('/{id}/edit', [\App\Http\Controllers\ShippingZoneController::class, 'edit'])->name('edit');
            Route::post('/', [\App\Http\Controllers\ShippingZoneController::class, 'store'])->name('store');
            Route::post('/bulk-delete', [\App\Http\Controllers\ShippingZoneController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/{id}', [\App\Http\Controllers\ShippingZoneController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\ShippingZoneController::class, 'destroy'])->name('destroy');
        });
        
        // Shipping Methods
        Route::prefix('methods')->name('methods.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ShippingMethodController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\ShippingMethodController::class, 'getData'])->name('data');
            Route::get('/{id}/edit', [\App\Http\Controllers\ShippingMethodController::class, 'edit'])->name('edit');
            Route::post('/', [\App\Http\Controllers\ShippingMethodController::class, 'store'])->name('store');
            Route::post('/bulk-delete', [\App\Http\Controllers\ShippingMethodController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/{id}', [\App\Http\Controllers\ShippingMethodController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\ShippingMethodController::class, 'destroy'])->name('destroy');
        });
        
        // Shipping Rates
        Route::prefix('rates')->name('rates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ShippingRateController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\ShippingRateController::class, 'getData'])->name('data');
            Route::get('/{id}/edit', [\App\Http\Controllers\ShippingRateController::class, 'edit'])->name('edit');
            Route::post('/', [\App\Http\Controllers\ShippingRateController::class, 'store'])->name('store');
            Route::post('/bulk-delete', [\App\Http\Controllers\ShippingRateController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/{id}', [\App\Http\Controllers\ShippingRateController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\ShippingRateController::class, 'destroy'])->name('destroy');
        });
    });
    
    // Field Management Routes
    Route::get('/field-management', [\App\Http\Controllers\FieldManagementController::class, 'index'])->name('field-management.index');
    Route::get('/field-management/data', [\App\Http\Controllers\FieldManagementController::class, 'getData'])->name('field-management.data');
    Route::get('/field-management/fields', [\App\Http\Controllers\FieldManagementController::class, 'getFieldsForForm'])->name('field-management.fields');
    Route::get('/field-management/all-fields', [\App\Http\Controllers\FieldManagementController::class, 'getAllFieldsForPreview'])->name('field-management.all-fields');
    Route::post('/field-management/seed', [\App\Http\Controllers\FieldManagementController::class, 'seedInitialData'])->name('field-management.seed');
    Route::post('/field-management/sync-system-fields', [\App\Http\Controllers\FieldManagementController::class, 'syncSystemFields'])->name('field-management.sync-system-fields');
    Route::post('/field-management/{id}/toggle-status', [\App\Http\Controllers\FieldManagementController::class, 'toggleStatus'])->name('field-management.toggle-status');
    Route::post('/field-management/{id}/toggle-visible', [\App\Http\Controllers\FieldManagementController::class, 'toggleVisible'])->name('field-management.toggle-visible');
    Route::post('/field-management/{id}/toggle-required', [\App\Http\Controllers\FieldManagementController::class, 'toggleRequired'])->name('field-management.toggle-required');
    Route::post('/field-management/{fieldKey}/update-order', [\App\Http\Controllers\FieldManagementController::class, 'updateOrder'])->name('field-management.update-order');
    Route::get('/field-management/{id}/edit', [\App\Http\Controllers\FieldManagementController::class, 'edit'])->name('field-management.edit');
    Route::post('/field-management', [\App\Http\Controllers\FieldManagementController::class, 'store'])->name('field-management.store');
    Route::post('/field-management/{id}', [\App\Http\Controllers\FieldManagementController::class, 'update'])->name('field-management.update');
    Route::delete('/field-management/{id}', [\App\Http\Controllers\FieldManagementController::class, 'destroy'])->name('field-management.destroy');
    
    // Customer Routes
    Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/fields', [\App\Http\Controllers\CustomerController::class, 'getFields'])->name('customers.fields');
    Route::get('/customers/data', [\App\Http\Controllers\CustomerController::class, 'getData'])->name('customers.data');
    Route::get('/customers/{id}/edit', [\App\Http\Controllers\CustomerController::class, 'edit'])->name('customers.edit');
    Route::post('/customers', [\App\Http\Controllers\CustomerController::class, 'store'])->name('customers.store');
    Route::post('/customers/{id}', [\App\Http\Controllers\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [\App\Http\Controllers\CustomerController::class, 'destroy'])->name('customers.destroy');
    
    // Order Routes
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/data', [\App\Http\Controllers\OrderController::class, 'getData'])->name('orders.data');
    Route::get('/orders/customers', [\App\Http\Controllers\OrderController::class, 'getCustomers'])->name('orders.customers');
    Route::get('/orders/customers/{id}', [\App\Http\Controllers\OrderController::class, 'getCustomerDetails'])->name('orders.customer.details');
    Route::get('/orders/products', [\App\Http\Controllers\OrderController::class, 'getProducts'])->name('orders.products');
    Route::get('/orders/warehouses', [\App\Http\Controllers\OrderController::class, 'getWarehouses'])->name('orders.warehouses');
    Route::get('/orders/stock-availability', [\App\Http\Controllers\OrderController::class, 'getStockAvailability'])->name('orders.stock-availability');
    Route::get('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{id}/edit', [\App\Http\Controllers\OrderController::class, 'edit'])->name('orders.edit');
    Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{id}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::match(['post', 'put'], '/orders/{id}', [\App\Http\Controllers\OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'destroy'])->name('orders.destroy');
    
    // Coupons
    Route::get('/coupons', [\App\Http\Controllers\CouponController::class, 'index'])->name('coupons.index');
    Route::get('/coupons/data', [\App\Http\Controllers\CouponController::class, 'getData'])->name('coupons.data');
    Route::get('/coupons/generate-code', [\App\Http\Controllers\CouponController::class, 'generateCode'])->name('coupons.generateCode');
    Route::post('/coupons/validate-code', [\App\Http\Controllers\CouponController::class, 'validateCode'])->name('coupons.validateCode');
    Route::post('/coupons', [\App\Http\Controllers\CouponController::class, 'store'])->name('coupons.store');
    Route::get('/coupons/{id}/edit', [\App\Http\Controllers\CouponController::class, 'edit'])->name('coupons.edit');
    Route::post('/coupons/{id}', [\App\Http\Controllers\CouponController::class, 'update'])->name('coupons.update');
    Route::post('/coupons/{id}/toggle-status', [\App\Http\Controllers\CouponController::class, 'toggleStatus'])->name('coupons.toggleStatus');
    Route::delete('/coupons/{id}', [\App\Http\Controllers\CouponController::class, 'destroy'])->name('coupons.destroy');
    
    // Carts
    Route::get('/carts', [\App\Http\Controllers\CartController::class, 'index'])->name('carts.index');
    Route::get('/carts/data', [\App\Http\Controllers\CartController::class, 'getData'])->name('carts.data');
    Route::get('/carts/{id}', [\App\Http\Controllers\CartController::class, 'show'])->name('carts.show');
    Route::delete('/carts/{id}', [\App\Http\Controllers\CartController::class, 'destroy'])->name('carts.destroy');
});
