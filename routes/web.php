<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\SectionManagementController; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail; 


Route::get('/test-db', function () {
    return DB::table('inventory_stocks')->whereIn('product_variant_id', ['15','16','19'])->get();  
});

Route::get('/debug-log', function () {
    Log::info('Manual log trigger executed', [
        'source' => 'debug-route',
        'timestamp' => now()->toDateTimeString(),
    ]);

    return 'Log entry created.';
});

// Frontend Routes
//assets for this are located in "public/frontend/"
Route::get('/', [FrontendController::class, 'index'])->name('frontend.index');
Route::get('/shop', [FrontendController::class, 'shop'])->name('frontend.shop');
Route::get('/shop/load-more', [FrontendController::class, 'loadMoreProducts'])->name('frontend.shop.load-more');
Route::get('/product', [FrontendController::class, 'product'])->name('frontend.product');
Route::get('/api/product-quick-view', [FrontendController::class, 'getProductQuickView'])->name('frontend.product.quickview');
Route::get('/about-us', [FrontendController::class, 'aboutUs'])->name('frontend.about-us');
Route::get('/contact', [FrontendController::class, 'contact'])->name('frontend.contact');
Route::post('/contact', [FrontendController::class, 'submitContact'])->name('frontend.contact.submit');
Route::post('/subscribe', [FrontendController::class, 'subscribe'])->name('frontend.subscribe');
Route::get('/privacy', [FrontendController::class, 'privacy'])->name('frontend.privacy'); 
Route::get('/terms-and-conditions', [FrontendController::class, 'termAndCondition'])->name('frontend.terms');
Route::get('/shipping-policy', [FrontendController::class, 'shipping'])->name('frontend.shipping');
Route::get('/cancellation-refund', [FrontendController::class, 'cancellationRefund'])->name('frontend.cancellation-refund');
Route::get('/return-refund-policy', [FrontendController::class, 'returnRefundPolicy'])->name('frontend.return-refund');
Route::get('/disclaimer', [FrontendController::class, 'disclaimer'])->name('frontend.disclaimer');
Route::get('/faq', [FrontendController::class, 'faq'])->name('frontend.faq');
Route::get('/blog', [FrontendController::class, 'blog'])->name('frontend.blog');
Route::get('/blog/{slug}', [FrontendController::class, 'blogDetail'])->name('frontend.blog-detail');
// Public Theme Listing (no auth) - GET /themes?software_id=xxx
Route::get('/themes', [\App\Http\Controllers\Api\ThemeController::class, 'publicIndex'])->name('themes.public');

// Public API Routes
Route::get('/api/location-by-pincode', [FrontendController::class, 'getLocationByPincode'])->name('frontend.location-by-pincode');

// Razorpay Webhook (Public - no auth required)
Route::post('/webhook/razorpay', [\App\Http\Controllers\RazorpayWebhookController::class, 'handle'])->name('webhook.razorpay');

// Review Routes (Public - get reviews)
Route::get('/api/reviews/product/{productId}', [\App\Http\Controllers\ReviewController::class, 'getProductReviews'])->name('reviews.product');
Route::get('/api/reviews/can-review/{productId}', [\App\Http\Controllers\ReviewController::class, 'canReview'])->name('reviews.can-review');

// Address API Routes (Protected - require customer authentication)
Route::middleware(['customer.auth'])->group(function () {
    Route::get('/api/address/{id}', [FrontendController::class, 'getAddress'])->name('frontend.address.get');
    Route::delete('/api/address/{id}', [FrontendController::class, 'deleteAddress'])->name('frontend.address.delete');
});

// Customer Dashboard Routes (Protected - require customer authentication)
Route::middleware(['customer.auth'])->group(function () { 
    Route::post('/api/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    
    Route::get('/my-orders', [FrontendController::class, 'myOrders'])->name('frontend.my-orders');
    Route::get('/my-orders/{id}/invoice', [\App\Http\Controllers\OrderController::class, 'invoice'])->name('frontend.orders.invoice');
    Route::post('/orders/{id}/cancel', [FrontendController::class, 'cancelOrder'])->name('frontend.orders.cancel');
    Route::get('/profile-info', [FrontendController::class, 'profileInfo'])->name('frontend.profile-info');
    Route::post('/profile-info', [FrontendController::class, 'updateProfileInfo'])->name('frontend.profile-info.update');
    Route::get('/change-password', [FrontendController::class, 'changePassword'])->name('frontend.change-password');
    Route::post('/change-password', [FrontendController::class, 'updatePassword'])->name('frontend.change-password.update');
    Route::get('/addresses', [FrontendController::class, 'addresses'])->name('frontend.addresses');
    Route::post('/addresses', [FrontendController::class, 'saveAddress'])->name('frontend.addresses.save');
    Route::get('/payment-methode', [FrontendController::class, 'paymentMethode'])->name('frontend.payment-methode');
    Route::get('/checkout', [FrontendController::class, 'checkout'])->name('frontend.checkout')->middleware('simple.checkout.prerequisites');
    Route::post('/checkout', [FrontendController::class, 'processCheckout'])->name('frontend.checkout.process')->middleware('simple.checkout.prerequisites');
    Route::post('/razorpay/create-order', [FrontendController::class, 'createRazorpayOrder'])->name('frontend.razorpay.create-order')->middleware('simple.checkout.prerequisites');
    Route::post('/razorpay/payment-success', [FrontendController::class, 'razorpayPaymentSuccess'])->name('frontend.razorpay.payment-success');
    
    // Temporary debug route without middleware
    Route::get('/checkout-debug', [FrontendController::class, 'checkout'])->name('frontend.checkout.debug');
     
    Route::get('/test-auth', function() {
        $customer = Auth::guard('customer')->user();
        return response()->json([
            'authenticated' => $customer ? true : false,
            'customer_id' => $customer ? $customer->id : null,
            'customer_name' => $customer ? $customer->full_name : null,
        ]);
    });

    Route::get('/complete-order/{order?}', [FrontendController::class, 'completeOrder'])->name('frontend.complete-order');
});

// Public Frontend Routes
Route::get('/shoping-cart', [FrontendController::class, 'shopingCart'])->name('frontend.shoping-cart');
Route::get('/wishlist', [FrontendController::class, 'wishlist'])->name('frontend.wishlist');

// ============================================================
// API Routes (moved from api.php to web.php for Blade usage)
// ============================================================

// Authentication API Routes
Route::prefix('api/auth')->group(function () {
    Route::get('/login-fields', [\App\Http\Controllers\auth\AuthApiController::class, 'getLoginFields']); // Get system fields for login (email, password)
    Route::get('/register-fields', [\App\Http\Controllers\auth\AuthApiController::class, 'getRegistrationFields']); // Get system fields for registration
    Route::post('/send-otp', [\App\Http\Controllers\auth\AuthApiController::class, 'sendOtp']); // Send OTP to email
    Route::post('/verify-otp', [\App\Http\Controllers\auth\AuthApiController::class, 'verifyOtp']); // Verify OTP
    Route::post('/register', [\App\Http\Controllers\auth\AuthApiController::class, 'register']); // Register new customer using dynamic field management
    Route::post('/login', [\App\Http\Controllers\auth\AuthApiController::class, 'login']); // Login customer (supports email OR phone)
    Route::get('/me', [\App\Http\Controllers\auth\AuthApiController::class, 'me']); // Get authenticated customer (optional auth)
    
    // Forgot Password Routes
    Route::post('/forgot-password/send-otp', [\App\Http\Controllers\auth\AuthApiController::class, 'forgotPasswordSendOtp']); // Send OTP for password reset
    Route::post('/forgot-password/verify-otp', [\App\Http\Controllers\auth\AuthApiController::class, 'forgotPasswordVerifyOtp']); // Verify OTP for password reset
    Route::post('/forgot-password/reset', [\App\Http\Controllers\auth\AuthApiController::class, 'resetPassword']); // Reset password
    
    Route::middleware('customer.auth')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\auth\AuthApiController::class, 'logout']); // Logout customer
        Route::post('/profile/send-email-otp', [\App\Http\Controllers\auth\AuthApiController::class, 'profileSendEmailOtp']); // Send OTP to new email for profile update
        Route::post('/profile/verify-email-otp', [\App\Http\Controllers\auth\AuthApiController::class, 'profileVerifyEmailOtp']); // Verify OTP for profile email update
    });
});

// Legacy user endpoint (for backward compatibility)
Route::get('/api/user', function (Request $request) {
    return Auth::guard('customer')->user();
})->middleware('customer.auth');

// Cart API Routes
Route::prefix('api/cart')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\CartApiController::class, 'index']); // Get cart summary
    Route::get('/count', [\App\Http\Controllers\Api\CartApiController::class, 'count']); // Get cart count
    Route::post('/items', [\App\Http\Controllers\Api\CartApiController::class, 'addItem']); // Add item to cart
    Route::put('/items/{itemId}', [\App\Http\Controllers\Api\CartApiController::class, 'updateItem']); // Update cart item quantity
    Route::delete('/items/{itemId}', [\App\Http\Controllers\Api\CartApiController::class, 'removeItem']); // Remove cart item
    Route::post('/coupon', [\App\Http\Controllers\Api\CartApiController::class, 'applyCoupon']); // Apply coupon
    Route::delete('/coupon', [\App\Http\Controllers\Api\CartApiController::class, 'removeCoupon']); // Remove coupon
});

// Wishlist API Routes
Route::prefix('api/wishlist')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\WishlistApiController::class, 'index']); // Get wishlist items
    Route::get('/count', [\App\Http\Controllers\Api\WishlistApiController::class, 'count']); // Get wishlist count
    Route::post('/', [\App\Http\Controllers\Api\WishlistApiController::class, 'store']); // Add product to wishlist
    Route::delete('/{id}', [\App\Http\Controllers\Api\WishlistApiController::class, 'destroy']); // Remove wishlist item by ID
    Route::delete('/product/{productId}', [\App\Http\Controllers\Api\WishlistApiController::class, 'removeByProduct']); // Remove wishlist item by product ID
});

// Order API Routes
Route::prefix('api/orders')->middleware('customer.auth')->group(function () {
    Route::get('/validate-cart', [\App\Http\Controllers\Api\OrderApiController::class, 'validateCart']);
    Route::get('/addresses', [\App\Http\Controllers\Api\OrderApiController::class, 'getAddresses']);
    Route::post('/validate-addresses', [\App\Http\Controllers\Api\OrderApiController::class, 'validateAddresses']);
    Route::post('/', [\App\Http\Controllers\Api\OrderApiController::class, 'store']); // Create order from cart
});

// Catalog API Routes
Route::prefix('api/catalog')->group(function () {
    // Brands
    Route::get('/brands', [\App\Http\Controllers\Api\CatalogApiController::class, 'getBrands']);
    Route::get('/brands/{identifier}', [\App\Http\Controllers\Api\CatalogApiController::class, 'getBrand']);
    Route::get('/brands/{identifier}/categories', [\App\Http\Controllers\Api\CatalogApiController::class, 'getBrandCategories']);
    
    // Categories
    Route::get('/categories', [\App\Http\Controllers\Api\CatalogApiController::class, 'getCategories']);
    Route::get('/categories/{identifier}', [\App\Http\Controllers\Api\CatalogApiController::class, 'getCategory']);
    Route::get('/categories/{identifier}/children', [\App\Http\Controllers\Api\CatalogApiController::class, 'getCategoryChildren']);
    
    // Products
    Route::get('/products', [\App\Http\Controllers\Api\CatalogApiController::class, 'getProducts']);
    Route::get('/products/{identifier}', [\App\Http\Controllers\Api\CatalogApiController::class, 'getProduct']);
    
    // Variant Pricing - Centralized pricing endpoint
    Route::get('/variants/{variantId}/pricing', [FrontendController::class, 'getVariantPricing'])->name('api.variant.pricing');
});
 
Route::prefix('api/sections')->group(function () { 
    Route::get('/', [\App\Http\Controllers\Api\SectionsApiController::class, 'getSections']);
});

Route::get('/admin', [AuthController::class, 'showLogin'])->name('admin.login');
Route::get('/login', function() {
    return redirect()->route('admin.login');
})->name('login');
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
    Route::get('/brands', [\App\Http\Controllers\BrandController::class, 'index'])->middleware('permission:brands.view')->name('brands.index');
    Route::get('/brands/data', [\App\Http\Controllers\BrandController::class, 'getData'])->middleware('permission:brands.view')->name('brands.data');
    Route::get('/brands/{id}/edit', [\App\Http\Controllers\BrandController::class, 'edit'])->middleware('permission:brands.update')->name('brands.edit');
    Route::post('/brands', [\App\Http\Controllers\BrandController::class, 'store'])->middleware('permission:brands.create')->name('brands.store');
    Route::post('/brands/bulk-delete', [\App\Http\Controllers\BrandController::class, 'bulkDelete'])->middleware('permission:brands.delete')->name('brands.bulk-delete'); // Must be before /brands/{id}
    Route::post('/brands/{id}', [\App\Http\Controllers\BrandController::class, 'update'])->middleware('permission:brands.update')->name('brands.update');
    Route::delete('/brands/{id}', [\App\Http\Controllers\BrandController::class, 'destroy'])->middleware('permission:brands.delete')->name('brands.destroy');
    
    // Categories (GET and POST only)
    Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->middleware('permission:categories.view')->name('categories.index');
    Route::get('/categories/data', [\App\Http\Controllers\CategoryController::class, 'getData'])->middleware('permission:categories.view')->name('categories.data');
    Route::get('/categories/parents', [\App\Http\Controllers\CategoryController::class, 'getParents'])->middleware('permission:categories.view')->name('categories.parents');
    Route::get('/categories/children', [\App\Http\Controllers\CategoryController::class, 'getChildren'])->middleware('permission:categories.view')->name('categories.children');
    Route::get('/categories/attributes', [\App\Http\Controllers\CategoryController::class, 'getAvailableAttributes'])->middleware('permission:categories.view')->name('categories.attributes');
    Route::get('/categories/edit', [\App\Http\Controllers\CategoryController::class, 'edit'])->middleware('permission:categories.update')->name('categories.edit');
    Route::post('/categories/store', [\App\Http\Controllers\CategoryController::class, 'store'])->middleware('permission:categories.create')->name('categories.store');
    Route::post('/categories/bulk-store', [\App\Http\Controllers\CategoryController::class, 'bulkStore'])->middleware('permission:categories.create')->name('categories.bulk-store');
    Route::post('/categories/update', [\App\Http\Controllers\CategoryController::class, 'update'])->middleware('permission:categories.update')->name('categories.update');
    Route::post('/categories/bulk-update', [\App\Http\Controllers\CategoryController::class, 'bulkUpdate'])->middleware('permission:categories.update')->name('categories.bulk-update');
    Route::post('/categories/bulk-sync', [\App\Http\Controllers\CategoryController::class, 'bulkSync'])->middleware('permission:categories.create')->name('categories.bulk-sync');
    Route::post('/categories/delete', [\App\Http\Controllers\CategoryController::class, 'delete'])->middleware('permission:categories.delete')->name('categories.delete');
    Route::post('/categories/restore', [\App\Http\Controllers\CategoryController::class, 'restore'])->middleware('permission:categories.update')->name('categories.restore');
    Route::post('/categories/bulk-delete', [\App\Http\Controllers\CategoryController::class, 'bulkDelete'])->middleware('permission:categories.delete')->name('categories.bulk-delete');
    Route::post('/categories/update-status', [\App\Http\Controllers\CategoryController::class, 'updateStatus'])->middleware('permission:categories.update')->name('categories.updateStatus');
    Route::post('/categories/update-featured', [\App\Http\Controllers\CategoryController::class, 'updateFeatured'])->middleware('permission:categories.update')->name('categories.updateFeatured');
    Route::post('/categories/update-parent', [\App\Http\Controllers\CategoryController::class, 'updateParent'])->middleware('permission:categories.update')->name('categories.updateParent');
    
    // Home Sliders Management
    Route::get('/home-sliders', [\App\Http\Controllers\HomeSliderController::class, 'index'])->middleware('permission:website_management.view')->name('home-sliders.index');
    Route::get('/home-sliders/data', [\App\Http\Controllers\HomeSliderController::class, 'getData'])->middleware('permission:website_management.view')->name('home-sliders.data');
    Route::post('/home-sliders/update-sort-order', [\App\Http\Controllers\HomeSliderController::class, 'updateSortOrder'])->middleware('permission:website_management.update')->name('home-sliders.update-sort-order');
    Route::post('/home-sliders/{id}/update-status', [\App\Http\Controllers\HomeSliderController::class, 'updateStatus'])->middleware('permission:website_management.update')->name('home-sliders.update-status');
    Route::get('/home-sliders/{id}', [\App\Http\Controllers\HomeSliderController::class, 'show'])->middleware('permission:website_management.view')->name('home-sliders.show');
    Route::post('/home-sliders', [\App\Http\Controllers\HomeSliderController::class, 'store'])->middleware('permission:website_management.create')->name('home-sliders.store');
    Route::match(['post', 'put'], '/home-sliders/{id}', [\App\Http\Controllers\HomeSliderController::class, 'update'])->middleware('permission:website_management.update')->name('home-sliders.update');
    Route::delete('/home-sliders/{id}', [\App\Http\Controllers\HomeSliderController::class, 'destroy'])->middleware('permission:website_management.delete')->name('home-sliders.destroy');
    
    // Featured Category Style Management
    Route::get('/featured-category-style', [\App\Http\Controllers\FeaturedCategoryStyleController::class, 'index'])->middleware('permission:website_management.view')->name('featured-category-style.index');
    Route::get('/featured-category-style/data', [\App\Http\Controllers\FeaturedCategoryStyleController::class, 'getData'])->middleware('permission:website_management.view')->name('featured-category-style.data');
    Route::post('/featured-category-style/update-sort-order', [\App\Http\Controllers\FeaturedCategoryStyleController::class, 'updateSortOrder'])->middleware('permission:website_management.update')->name('featured-category-style.update-sort-order');
    Route::get('/featured-category-style/{id}', [\App\Http\Controllers\FeaturedCategoryStyleController::class, 'show'])->middleware('permission:website_management.view')->name('featured-category-style.show');
    Route::post('/featured-category-style', [\App\Http\Controllers\FeaturedCategoryStyleController::class, 'store'])->middleware('permission:website_management.create')->name('featured-category-style.store');
    Route::match(['post', 'put'], '/featured-category-style/{id}', [\App\Http\Controllers\FeaturedCategoryStyleController::class, 'update'])->middleware('permission:website_management.update')->name('featured-category-style.update');
    Route::delete('/featured-category-style/{id}', [\App\Http\Controllers\FeaturedCategoryStyleController::class, 'destroy'])->middleware('permission:website_management.delete')->name('featured-category-style.destroy');
    
    // Our Collection Management
    Route::get('/our-collection', [\App\Http\Controllers\OurCollectionController::class, 'index'])->middleware('permission:website_management.view')->name('our-collection.index');
    Route::post('/our-collection/update', [\App\Http\Controllers\OurCollectionController::class, 'update'])->middleware('permission:website_management.update')->name('our-collection.update');
    
    // Testimonials Management
    Route::get('/testimonials', [\App\Http\Controllers\TestimonialController::class, 'index'])->middleware('permission:website_management.view')->name('testimonials.index');
    Route::get('/testimonials/data', [\App\Http\Controllers\TestimonialController::class, 'getData'])->middleware('permission:website_management.view')->name('testimonials.data');
    Route::post('/testimonials/update-sort-order', [\App\Http\Controllers\TestimonialController::class, 'updateSortOrder'])->middleware('permission:website_management.update')->name('testimonials.update-sort-order');
    Route::get('/testimonials/{id}', [\App\Http\Controllers\TestimonialController::class, 'show'])->middleware('permission:website_management.view')->name('testimonials.show');
    Route::post('/testimonials', [\App\Http\Controllers\TestimonialController::class, 'store'])->middleware('permission:website_management.create')->name('testimonials.store');
    Route::match(['post', 'put'], '/testimonials/{id}', [\App\Http\Controllers\TestimonialController::class, 'update'])->middleware('permission:website_management.update')->name('testimonials.update');
    Route::delete('/testimonials/{id}', [\App\Http\Controllers\TestimonialController::class, 'destroy'])->middleware('permission:website_management.delete')->name('testimonials.destroy');

    // Instagram Gallery Management
    Route::get('/instagram-gallery', [\App\Http\Controllers\InstagramGalleryController::class, 'index'])->middleware('permission:website_management.view')->name('instagram-gallery.index');
    Route::get('/instagram-gallery/data', [\App\Http\Controllers\InstagramGalleryController::class, 'getData'])->middleware('permission:website_management.view')->name('instagram-gallery.data');
    Route::post('/instagram-gallery/bulk', [\App\Http\Controllers\InstagramGalleryController::class, 'storeBulk'])->middleware('permission:website_management.create')->name('instagram-gallery.store-bulk');
    Route::post('/instagram-gallery/update-sort-order', [\App\Http\Controllers\InstagramGalleryController::class, 'updateSortOrder'])->middleware('permission:website_management.update')->name('instagram-gallery.update-sort-order');
    Route::get('/instagram-gallery/{id}', [\App\Http\Controllers\InstagramGalleryController::class, 'show'])->middleware('permission:website_management.view')->name('instagram-gallery.show');
    Route::post('/instagram-gallery', [\App\Http\Controllers\InstagramGalleryController::class, 'store'])->middleware('permission:website_management.create')->name('instagram-gallery.store');
    Route::match(['post', 'put'], '/instagram-gallery/{id}', [\App\Http\Controllers\InstagramGalleryController::class, 'update'])->middleware('permission:website_management.update')->name('instagram-gallery.update');
    Route::post('/instagram-gallery/{id}/status', [\App\Http\Controllers\InstagramGalleryController::class, 'updateStatus'])->middleware('permission:website_management.update')->name('instagram-gallery.update-status');
    Route::delete('/instagram-gallery/{id}', [\App\Http\Controllers\InstagramGalleryController::class, 'destroy'])->middleware('permission:website_management.delete')->name('instagram-gallery.destroy');

    // Service Highlights Management
    Route::get('/service-highlights', [\App\Http\Controllers\ServiceHighlightController::class, 'index'])->middleware('permission:website_management.view')->name('service-highlights.index');
    Route::post('/service-highlights/update', [\App\Http\Controllers\ServiceHighlightController::class, 'update'])->middleware('permission:website_management.update')->name('service-highlights.update');
    
    // Reviews Management
    Route::get('/reviews', [\App\Http\Controllers\Admin\AdminReviewController::class, 'index'])->middleware('permission:website_management.view')->name('reviews.index');
    Route::get('/reviews/data', [\App\Http\Controllers\Admin\AdminReviewController::class, 'getData'])->middleware('permission:website_management.view')->name('reviews.data');
    Route::post('/reviews/{id}/update-status', [\App\Http\Controllers\Admin\AdminReviewController::class, 'updateStatus'])->middleware('permission:website_management.update')->name('reviews.update-status');
    Route::post('/reviews/bulk-update-status', [\App\Http\Controllers\Admin\AdminReviewController::class, 'bulkUpdateStatus'])->middleware('permission:website_management.update')->name('reviews.bulk-update-status');
    Route::delete('/reviews/{id}', [\App\Http\Controllers\Admin\AdminReviewController::class, 'destroy'])->middleware('permission:website_management.delete')->name('reviews.destroy');
    Route::post('/reviews/bulk-delete', [\App\Http\Controllers\Admin\AdminReviewController::class, 'bulkDelete'])->middleware('permission:website_management.delete')->name('reviews.bulk-delete');
    
    // About Us Management
    Route::get('/admin/about-us', [\App\Http\Controllers\AboutUsController::class, 'index'])->middleware('permission:website_management.view')->name('about-us.index');
    Route::post('/admin/about-us/update', [\App\Http\Controllers\AboutUsController::class, 'update'])->middleware('permission:website_management.update')->name('about-us.update');
    
    // Legal Pages Management
    Route::get('/admin/legal-pages', [\App\Http\Controllers\LegalPageController::class, 'index'])->middleware('permission:website_management.view')->name('legal-pages.index');
    Route::post('/admin/legal-pages/update', [\App\Http\Controllers\LegalPageController::class, 'update'])->middleware('permission:website_management.update')->name('legal-pages.update');
    
    // FAQ Management
    Route::get('/admin/faqs', [\App\Http\Controllers\FaqController::class, 'index'])->middleware('permission:website_management.view')->name('faqs.index');
    Route::get('/admin/themes', [\App\Http\Controllers\ThemeController::class, 'index'])->middleware('permission:website_management.view')->name('themes.index');
    Route::post('/admin/themes/software-id', [\App\Http\Controllers\ThemeController::class, 'updateSoftwareId'])->middleware('permission:website_management.update')->name('themes.update-software-id');
    Route::post('/admin/faqs', [\App\Http\Controllers\FaqController::class, 'store'])->middleware('permission:website_management.create')->name('faqs.store');
    Route::put('/admin/faqs/{id}', [\App\Http\Controllers\FaqController::class, 'update'])->middleware('permission:website_management.update')->name('faqs.update');
    Route::delete('/admin/faqs/{id}', [\App\Http\Controllers\FaqController::class, 'destroy'])->middleware('permission:website_management.delete')->name('faqs.destroy');
    Route::post('/admin/faqs/{id}/toggle-status', [\App\Http\Controllers\FaqController::class, 'toggleStatus'])->middleware('permission:website_management.update')->name('faqs.toggle-status');
    
    // Blogs Management
    Route::get('/admin/blogs', [\App\Http\Controllers\BlogController::class, 'index'])->middleware('permission:website_management.view')->name('blogs.index');
    Route::get('/admin/blogs/data', [\App\Http\Controllers\BlogController::class, 'getData'])->middleware('permission:website_management.view')->name('blogs.data');
    Route::get('/admin/blogs/create', [\App\Http\Controllers\BlogController::class, 'create'])->middleware('permission:website_management.view')->name('blogs.create');
    Route::post('/admin/blogs', [\App\Http\Controllers\BlogController::class, 'store'])->middleware('permission:website_management.create')->name('blogs.store');
    Route::get('/admin/blogs/{id}', [\App\Http\Controllers\BlogController::class, 'show'])->middleware('permission:website_management.view')->name('blogs.show');
    Route::get('/admin/blogs/{id}/edit', [\App\Http\Controllers\BlogController::class, 'edit'])->middleware('permission:website_management.view')->name('blogs.edit');
    Route::match(['post', 'put'], '/admin/blogs/{id}', [\App\Http\Controllers\BlogController::class, 'update'])->middleware('permission:website_management.update')->name('blogs.update');
    Route::delete('/admin/blogs/{id}', [\App\Http\Controllers\BlogController::class, 'destroy'])->middleware('permission:website_management.delete')->name('blogs.destroy');


    // Contact Management
    Route::get('/admin/contacts', [\App\Http\Controllers\ContactController::class, 'index'])->middleware('permission:contact_messages.view')->name('contacts.index');
    Route::get('/admin/contacts/data', [\App\Http\Controllers\ContactController::class, 'getData'])->middleware('permission:contact_messages.view')->name('contacts.data');
    Route::get('/admin/contacts/{id}', [\App\Http\Controllers\ContactController::class, 'show'])->middleware('permission:contact_messages.view')->name('contacts.show');
    Route::post('/admin/contacts/{id}/toggle-read', [\App\Http\Controllers\ContactController::class, 'toggleRead'])->middleware('permission:contact_messages.update')->name('contacts.toggle-read');
    Route::delete('/admin/contacts/{id}', [\App\Http\Controllers\ContactController::class, 'destroy'])->middleware('permission:contact_messages.delete')->name('contacts.destroy');
    
    // Profile (GET and POST only)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->middleware('permission:company_profile.view')->name('profile.index');
    Route::post('/profile/update-image', [\App\Http\Controllers\ProfileController::class, 'updateImage'])->middleware('permission:company_profile.update')->name('profile.updateImage');
    Route::post('/profile/update-name', [\App\Http\Controllers\ProfileController::class, 'updateName'])->middleware('permission:company_profile.update')->name('profile.updateName');
    Route::post('/profile/update-email', [\App\Http\Controllers\ProfileController::class, 'updateEmail'])->middleware('permission:company_profile.update')->name('profile.updateEmail');
    Route::post('/profile/update-password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->middleware('permission:company_profile.update')->name('profile.updatePassword');
    Route::post('/profile/update-company-settings', [\App\Http\Controllers\ProfileController::class, 'updateCompanySettings'])->middleware('permission:company_profile.update')->name('profile.updateCompanySettings');
    Route::post('/profile/toggle-coming-soon', [\App\Http\Controllers\ProfileController::class, 'toggleComingSoon'])->middleware('permission:company_profile.update')->name('profile.toggleComingSoon');
    
    // Permissions (GET and POST only)
    Route::get('/permissions', [\App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/data', [\App\Http\Controllers\PermissionController::class, 'getData'])->name('permissions.data');
    Route::get('/permissions/edit', [\App\Http\Controllers\PermissionController::class, 'edit'])->name('permissions.edit');
    Route::post('/permissions/store', [\App\Http\Controllers\PermissionController::class, 'store'])->name('permissions.store');
    Route::post('/permissions/update', [\App\Http\Controllers\PermissionController::class, 'update'])->name('permissions.update');
    Route::post('/permissions/update-sort', [\App\Http\Controllers\PermissionController::class, 'updateSort'])->name('permissions.updateSort');
    
    // Roles (GET and POST only)
    Route::get('/roles', [\App\Http\Controllers\RoleController::class, 'index'])->middleware('permission:role_permission.view')->name('roles.index');
    Route::get('/roles/data', [\App\Http\Controllers\RoleController::class, 'getData'])->middleware('permission:role_permission.view')->name('roles.data');
    Route::get('/roles/edit', [\App\Http\Controllers\RoleController::class, 'edit'])->middleware('permission:role_permission.update')->name('roles.edit');
    Route::get('/roles/permissions', [\App\Http\Controllers\RoleController::class, 'getPermissions'])->middleware('permission:role_permission.assign')->name('roles.permissions');
    Route::post('/roles/store', [\App\Http\Controllers\RoleController::class, 'store'])->middleware('permission:role_permission.create')->name('roles.store');
    Route::post('/roles/update', [\App\Http\Controllers\RoleController::class, 'update'])->middleware('permission:role_permission.update')->name('roles.update');
    Route::post('/roles/delete', [\App\Http\Controllers\RoleController::class, 'delete'])->middleware('permission:role_permission.delete')->name('roles.delete');
    Route::post('/roles/assign-users', [\App\Http\Controllers\RoleController::class, 'assignUsers'])->middleware('permission:role_permission.assign')->name('roles.assignUsers');
    
    
    // Users (GET and POST only)
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->middleware('permission:user.view')->name('users.index');
    Route::get('/users/data', [\App\Http\Controllers\UserController::class, 'getData'])->middleware('permission:user.view')->name('users.data');
    Route::get('/users/edit', [\App\Http\Controllers\UserController::class, 'edit'])->middleware('permission:user.update')->name('users.edit');
    Route::get('/users/roles', [\App\Http\Controllers\UserController::class, 'getRoles'])->middleware('permission:user.assign')->name('users.roles');
    Route::post('/users/store', [\App\Http\Controllers\UserController::class, 'store'])->middleware('permission:user.create')->name('users.store');
    Route::post('/users/update', [\App\Http\Controllers\UserController::class, 'update'])->middleware('permission:user.update')->name('users.update');
    Route::post('/users/delete', [\App\Http\Controllers\UserController::class, 'delete'])->middleware('permission:user.delete')->name('users.delete');
    
    // Sections
    Route::get('/sections', [\App\Http\Controllers\SectionController::class, 'index'])->middleware('permission:section_management.view')->name('sections.index');
    
    // Pages
    Route::get('/sections/pages', [\App\Http\Controllers\SectionController::class, 'getPages'])->middleware('permission:section_management.view')->name('sections.pages');
    Route::get('/sections/pages/edit', [\App\Http\Controllers\SectionController::class, 'editPage'])->middleware('permission:section_management.update')->name('sections.pages.edit');
    Route::post('/sections/pages/store', [\App\Http\Controllers\SectionController::class, 'storePage'])->middleware('permission:section_management.update')->name('sections.pages.store');
    Route::post('/sections/pages/update', [\App\Http\Controllers\SectionController::class, 'updatePage'])->middleware('permission:section_management.update')->name('sections.pages.update');
    Route::post('/sections/pages/delete', [\App\Http\Controllers\SectionController::class, 'deletePage'])->middleware('permission:section_management.update')->name('sections.pages.delete');
    Route::post('/sections/pages/update-sort-order', [\App\Http\Controllers\SectionController::class, 'updatePagesSortOrder'])->middleware('permission:section_management.update')->name('sections.pages.updateSortOrder');
        
    // Sections
    Route::get('/sections/get', [\App\Http\Controllers\SectionController::class, 'getSections'])->middleware('permission:section_management.view')->name('sections.get');
    Route::get('/sections/home', [\App\Http\Controllers\SectionController::class, 'getHomePageSections'])->middleware('permission:section_management.view')->name('sections.home');
    Route::get('/sections/page', [\App\Http\Controllers\SectionController::class, 'getPageSections'])->middleware('permission:section_management.view')->name('sections.page');
    Route::get('/sections/edit', [\App\Http\Controllers\SectionController::class, 'edit'])->middleware('permission:section_management.update')->name('sections.edit');
    Route::post('/sections/store', [\App\Http\Controllers\SectionController::class, 'store'])->middleware('permission:section_management.update')->name('sections.store');
    Route::post('/sections/update', [\App\Http\Controllers\SectionController::class, 'update'])->middleware('permission:section_management.update')->name('sections.update');
    Route::post('/sections/delete', [\App\Http\Controllers\SectionController::class, 'delete'])->middleware('permission:section_management.update')->name('sections.delete');
    Route::post('/sections/toggle-variant', [\App\Http\Controllers\SectionController::class, 'toggleVariant'])->middleware('permission:section_management.update')->name('sections.toggleVariant');
    Route::post('/sections/update-variant-image', [\App\Http\Controllers\SectionController::class, 'updateVariantImage'])->middleware('permission:section_management.update')->name('sections.updateVariantImage');
    Route::post('/sections/update-sort-order', [\App\Http\Controllers\SectionController::class, 'updateSortOrder'])->middleware('permission:section_management.update')->name('sections.updateSortOrder');
    Route::post('/sections/update-font-family', [\App\Http\Controllers\SectionController::class, 'updateFontFamily'])->middleware('permission:section_management.update')->name('sections.updateFontFamily');
    Route::post('/sections/update-color-theme', [\App\Http\Controllers\SectionController::class, 'updateColorTheme'])->middleware('permission:section_management.update')->name('sections.updateColorTheme');
    Route::post('/sections/initialize-home', [\App\Http\Controllers\SectionController::class, 'initializeHomePageSections'])->middleware('permission:section_management.update')->name('sections.initializeHome');
    
    // Products
    Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->middleware('permission:product_master.view')->name('products.index');
    Route::get('/products/create', [\App\Http\Controllers\ProductController::class, 'create'])->middleware('permission:product_master.create')->name('products.create');
    Route::get('/products/quick-create', [\App\Http\Controllers\ProductController::class, 'quickCreate'])->middleware('permission:product_master.create')->name('products.quick-create');
    Route::post('/products/quick-store', [\App\Http\Controllers\ProductController::class, 'quickStore'])->middleware('permission:product_master.create')->name('products.quick-store');
    Route::post('/products/import', [ProductImportController::class, 'store'])->middleware('permission:product_master.import')->name('products.import');
    
    // Export routes
    Route::get('/exports/brands', [\App\Http\Controllers\ExportController::class, 'exportBrands'])->middleware('permission:product_master.export')->name('exports.brands');
    Route::get('/exports/categories', [\App\Http\Controllers\ExportController::class, 'exportCategories'])->middleware('permission:product_master.export')->name('exports.categories');
    Route::get('/exports/products', [\App\Http\Controllers\ExportController::class, 'exportProducts'])->middleware('permission:product_master.export')->name('exports.products');
    Route::get('/exports/variants', [\App\Http\Controllers\ExportController::class, 'exportVariants'])->middleware('permission:product_master.export')->name('exports.variants');
    Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store'])->middleware('permission:product_master.create')->name('products.store');
    Route::get('/products/data', [\App\Http\Controllers\ProductController::class, 'getData'])->middleware('permission:product_master.view')->name('products.data');
    Route::get('/products/attributes', [\App\Http\Controllers\ProductController::class, 'getAttributes'])->middleware('permission:product_master.view')->name('products.attributes');
    Route::get('/products/attributes-by-category', [\App\Http\Controllers\ProductController::class, 'getAttributesByCategory'])->middleware('permission:product_master.view')->name('products.attributes-by-category');
    Route::get('/products/categories-by-brand', [\App\Http\Controllers\ProductController::class, 'getCategoriesByBrand'])->middleware('permission:product_master.view')->name('products.categories-by-brand');
    Route::get('/products/units-by-type', [\App\Http\Controllers\ProductController::class, 'getUnitsByType'])->middleware('permission:product_master.view')->name('products.units-by-type');
    Route::get('/products/search', [\App\Http\Controllers\ProductController::class, 'search'])->middleware('permission:product_master.view')->name('products.search');
    Route::get('/products/{product}', [\App\Http\Controllers\ProductController::class, 'show'])->middleware('permission:product_master.view')->name('products.show');
    Route::get('/products/{product}/edit', [\App\Http\Controllers\ProductController::class, 'edit'])->middleware('permission:product_master.update')->name('products.edit');
    Route::get('/products/{product}/variants', [\App\Http\Controllers\ProductController::class, 'manageVariants'])->middleware('permission:product_master.update')->name('products.variants');
    Route::post('/products/{product}/variants/update', [\App\Http\Controllers\ProductController::class, 'updateVariants'])->middleware('permission:product_master.update')->name('products.variants.update');
    Route::post('/products/{product}/update', [\App\Http\Controllers\ProductController::class, 'update'])->middleware('permission:product_master.update')->name('products.update');
    Route::post('/products/{product}/delete', [\App\Http\Controllers\ProductController::class, 'destroy'])->middleware('permission:product_master.delete')->name('products.destroy');
    Route::post('/products/bulk-delete', [\App\Http\Controllers\ProductController::class, 'bulkDelete'])->middleware('permission:product_master.delete')->name('products.bulk-delete');
    Route::post('/products/{product}/toggle-status', [\App\Http\Controllers\ProductController::class, 'toggleStatus'])->middleware('permission:product_master.update')->name('products.toggleStatus');
    Route::post('/products/{product}/toggle-featured', [\App\Http\Controllers\ProductController::class, 'toggleFeatured'])->middleware('permission:product_master.update')->name('products.toggleFeatured');
    Route::post('/products/{product}/generate-variants', [\App\Http\Controllers\ProductController::class, 'generateVariants'])->middleware('permission:product_master.update')->name('products.generateVariants');
    Route::post('/products/delete-variant-image', [\App\Http\Controllers\ProductController::class, 'deleteVariantImage'])->middleware('permission:product_master.update')->name('products.deleteVariantImage');
    Route::get('/products/{product}/seo', [\App\Http\Controllers\ProductController::class, 'getSeo'])->middleware('permission:product_master.view')->name('products.seo.get');
    Route::put('/products/{product}/seo', [\App\Http\Controllers\ProductController::class, 'updateSeo'])->middleware('permission:product_master.update')->name('products.seo.update');
    
    // Variant Heading Suggestions API
    Route::get('/variant-headings/suggestions', [\App\Http\Controllers\ProductController::class, 'getHeadingSuggestions'])->name('variant-headings.suggestions');
    Route::post('/variant-headings/save-suggestion', [\App\Http\Controllers\ProductController::class, 'saveHeadingSuggestion'])->name('variant-headings.save-suggestion');
    
    // Inventory Management
    Route::get('/inventory', [\App\Http\Controllers\InventoryController::class, 'index'])->middleware('permission:inventory.view')->name('inventory.index');
    Route::get('/inventory/data', [\App\Http\Controllers\InventoryController::class, 'getData'])->middleware('permission:inventory.view')->name('inventory.data');
    Route::get('/inventory/sample', [\App\Http\Controllers\InventoryController::class, 'downloadSample'])->middleware('permission:inventory.import')->name('inventory.sample');
    Route::get('/inventory/export', [\App\Http\Controllers\InventoryController::class, 'export'])->middleware('permission:inventory.export')->name('inventory.export');
    Route::get('/inventory/warehouses', [\App\Http\Controllers\InventoryController::class, 'getWarehouses'])->middleware('permission:inventory.view')->name('inventory.warehouses');
    Route::get('/inventory/warehouses/{warehouseId}/locations', [\App\Http\Controllers\InventoryController::class, 'getWarehouseLocations'])->middleware('permission:inventory.view')->name('inventory.warehouse-locations');
    Route::get('/inventory/warehouse-codes-reference', [\App\Http\Controllers\InventoryController::class, 'getWarehouseCodesReference'])->middleware('permission:inventory.view')->name('inventory.warehouse-codes-reference');
    Route::get('/inventory/{variantId}/stock-breakdown', [\App\Http\Controllers\InventoryController::class, 'getStockBreakdown'])->middleware('permission:inventory.view')->name('inventory.stock-breakdown');
    Route::get('/inventory/{variantId}/history', [\App\Http\Controllers\InventoryController::class, 'getHistory'])->middleware('permission:inventory.view')->name('inventory.history');
    Route::post('/inventory/{variantId}/history/clear', [\App\Http\Controllers\InventoryController::class, 'clearHistory'])->middleware('permission:inventory.delete')->name('inventory.history.clear');
    Route::post('/inventory/bulk-add', [\App\Http\Controllers\InventoryController::class, 'bulkAddStock'])->middleware('permission:inventory.create')->name('inventory.bulk-add');
    Route::post('/inventory/import', [\App\Http\Controllers\InventoryController::class, 'import'])->middleware('permission:inventory.import')->name('inventory.import');
    Route::post('/inventory/{id}', [\App\Http\Controllers\InventoryController::class, 'update'])->middleware('permission:inventory.update')->name('inventory.update');
    
    // Lead Masters Management (under Master Data)
    Route::get('/lead-masters', [\App\Http\Controllers\LeadMasterController::class, 'index'])->middleware('permission:lead_masters.view')->name('lead-masters.index');
    Route::get('/lead-masters/data', [\App\Http\Controllers\LeadMasterController::class, 'getData'])->middleware('permission:lead_masters.view')->name('lead-masters.data');
    Route::post('/lead-masters/status', [\App\Http\Controllers\LeadMasterController::class, 'storeStatus'])->middleware('permission:lead_masters.create')->name('lead-masters.store-status');
    Route::post('/lead-masters/status/{id}', [\App\Http\Controllers\LeadMasterController::class, 'updateStatus'])->middleware('permission:lead_masters.update')->name('lead-masters.update-status');
    Route::post('/lead-masters/status/{id}/delete', [\App\Http\Controllers\LeadMasterController::class, 'deleteStatus'])->middleware('permission:lead_masters.delete')->name('lead-masters.delete-status');
    Route::post('/lead-masters/source', [\App\Http\Controllers\LeadMasterController::class, 'storeSource'])->middleware('permission:lead_masters.create')->name('lead-masters.store-source');
    Route::post('/lead-masters/source/{id}', [\App\Http\Controllers\LeadMasterController::class, 'updateSource'])->middleware('permission:lead_masters.update')->name('lead-masters.update-source');
    Route::post('/lead-masters/source/{id}/delete', [\App\Http\Controllers\LeadMasterController::class, 'deleteSource'])->middleware('permission:lead_masters.delete')->name('lead-masters.delete-source');
    Route::post('/lead-masters/priority', [\App\Http\Controllers\LeadMasterController::class, 'storePriority'])->middleware('permission:lead_masters.create')->name('lead-masters.store-priority');
    Route::post('/lead-masters/priority/{id}', [\App\Http\Controllers\LeadMasterController::class, 'updatePriority'])->middleware('permission:lead_masters.update')->name('lead-masters.update-priority');
    Route::post('/lead-masters/priority/{id}/delete', [\App\Http\Controllers\LeadMasterController::class, 'deletePriority'])->middleware('permission:lead_masters.delete')->name('lead-masters.delete-priority');
    Route::post('/lead-masters/tag', [\App\Http\Controllers\LeadMasterController::class, 'storeTag'])->middleware('permission:lead_masters.create')->name('lead-masters.store-tag');
    Route::post('/lead-masters/tag/{id}', [\App\Http\Controllers\LeadMasterController::class, 'updateTag'])->middleware('permission:lead_masters.update')->name('lead-masters.update-tag');
    Route::post('/lead-masters/tag/{id}/delete', [\App\Http\Controllers\LeadMasterController::class, 'deleteTag'])->middleware('permission:lead_masters.delete')->name('lead-masters.delete-tag');
    
    // Lead Management
    Route::get('/leads', [\App\Http\Controllers\LeadController::class, 'index'])->middleware('permission:leads.view')->name('leads.index');
    Route::get('/leads/master-data', [\App\Http\Controllers\LeadController::class, 'getMasterData'])->middleware('permission:leads.view')->name('leads.master-data');
    Route::post('/leads', [\App\Http\Controllers\LeadController::class, 'store'])->middleware('permission:leads.create')->name('leads.store');
    Route::get('/leads/{id}', [\App\Http\Controllers\LeadController::class, 'show'])->middleware('permission:leads.view')->name('leads.show');
    Route::post('/leads/{id}', [\App\Http\Controllers\LeadController::class, 'update'])->middleware('permission:leads.update')->name('leads.update');
    Route::delete('/leads/{id}', [\App\Http\Controllers\LeadController::class, 'destroy'])->middleware('permission:leads.delete')->name('leads.destroy');
    Route::post('/leads/{id}/status', [\App\Http\Controllers\LeadController::class, 'updateStatus'])->middleware('permission:leads.update')->name('leads.update-status');
    Route::post('/leads/{id}/assign', [\App\Http\Controllers\LeadController::class, 'assign'])->middleware('permission:leads.update')->name('leads.assign');
    Route::post('/leads/{id}/priority', [\App\Http\Controllers\LeadController::class, 'updatePriority'])->middleware('permission:leads.update')->name('leads.update-priority');
    Route::get('/leads/{id}/activities', [\App\Http\Controllers\LeadController::class, 'getActivities'])->middleware('permission:leads.view')->name('leads.activities');
    Route::post('/leads/{id}/activities', [\App\Http\Controllers\LeadController::class, 'storeActivity'])->middleware('permission:leads.update')->name('leads.store-activity');
    Route::post('/leads/{id}/followup', [\App\Http\Controllers\LeadController::class, 'storeFollowup'])->middleware('permission:leads.update')->name('leads.followup');
    Route::post('/leads/bulk-delete', [\App\Http\Controllers\LeadController::class, 'bulkDelete'])->middleware('permission:leads.delete')->name('leads.bulk-delete');
        
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
    Route::get('/units', [\App\Http\Controllers\UnitController::class, 'index'])->middleware('permission:units.view')->name('units.index');
    Route::get('/units/create', [\App\Http\Controllers\UnitController::class, 'create'])->middleware('permission:units.create')->name('units.create');
    Route::post('/units', [\App\Http\Controllers\UnitController::class, 'store'])->middleware('permission:units.create')->name('units.store');
    Route::get('/units/{unit}', [\App\Http\Controllers\UnitController::class, 'show'])->middleware('permission:units.view')->name('units.show');
    Route::get('/units/{unit}/edit', [\App\Http\Controllers\UnitController::class, 'edit'])->middleware('permission:units.update')->name('units.edit');
    Route::put('/units/{unit}', [\App\Http\Controllers\UnitController::class, 'update'])->middleware('permission:units.update')->name('units.update');
    Route::delete('/units/{unit}', [\App\Http\Controllers\UnitController::class, 'destroy'])->middleware('permission:units.delete')->name('units.destroy');
    Route::get('/units-by-type', [\App\Http\Controllers\UnitController::class, 'getByType'])->middleware('permission:units.view')->name('units.by-type');
    Route::post('/units/{unit}/toggle-status', [\App\Http\Controllers\UnitController::class, 'toggleStatus'])->middleware('permission:units.update')->name('units.toggle-status');
    Route::post('/units/bulk-delete', [\App\Http\Controllers\UnitController::class, 'bulkDelete'])->middleware('permission:units.delete')->name('units.bulk-delete');
    
    // Warehouses Management Routes (Master Data)
    Route::prefix('master-data/warehouses')->name('warehouses.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WarehouseController::class, 'index'])->middleware('permission:warehouse.view')->name('index');
        Route::get('/data', [\App\Http\Controllers\WarehouseController::class, 'getData'])->middleware('permission:warehouse.view')->name('data');
        Route::post('/', [\App\Http\Controllers\WarehouseController::class, 'store'])->middleware('permission:warehouse.create')->name('store');
        Route::post('/bulk-delete', [\App\Http\Controllers\WarehouseController::class, 'bulkDelete'])->middleware('permission:warehouse.delete')->name('bulk-delete');
        
        // Warehouse Locations Routes (must come before parameterized routes)
        Route::prefix('locations')->name('locations.')->group(function () {
            Route::get('/data', [\App\Http\Controllers\WarehouseLocationController::class, 'getData'])->middleware('permission:warehouse.view')->name('data');
            Route::get('/{id}/edit', [\App\Http\Controllers\WarehouseLocationController::class, 'edit'])->middleware('permission:warehouse.update')->name('edit');
            Route::post('/', [\App\Http\Controllers\WarehouseLocationController::class, 'store'])->middleware('permission:warehouse.create')->name('store');
            Route::post('/bulk-delete', [\App\Http\Controllers\WarehouseLocationController::class, 'bulkDelete'])->middleware('permission:warehouse.delete')->name('bulk-delete');
            Route::post('/{id}', [\App\Http\Controllers\WarehouseLocationController::class, 'update'])->middleware('permission:warehouse.update')->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\WarehouseLocationController::class, 'destroy'])->middleware('permission:warehouse.delete')->name('destroy');
        });
         
        Route::get('/{id}/edit', [\App\Http\Controllers\WarehouseController::class, 'edit'])->middleware('permission:warehouse.update')->name('edit');
        Route::post('/{id}', [\App\Http\Controllers\WarehouseController::class, 'update'])->middleware('permission:warehouse.update')->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\WarehouseController::class, 'destroy'])->middleware('permission:warehouse.delete')->name('destroy');
    });
    
    // Shipping Management Routes (Master Data)
    Route::prefix('master-data/shipping')->name('shipping.')->group(function () {
        // Shipping Zones
        Route::prefix('zones')->name('zones.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ShippingZoneController::class, 'index'])->middleware('permission:shipping_zones.view')->name('index');
            Route::get('/data', [\App\Http\Controllers\ShippingZoneController::class, 'getData'])->middleware('permission:shipping_zones.view')->name('data');
            Route::get('/{id}/edit', [\App\Http\Controllers\ShippingZoneController::class, 'edit'])->middleware('permission:shipping_zones.update')->name('edit');
            Route::post('/', [\App\Http\Controllers\ShippingZoneController::class, 'store'])->middleware('permission:shipping_zones.create')->name('store');
            Route::post('/bulk-delete', [\App\Http\Controllers\ShippingZoneController::class, 'bulkDelete'])->middleware('permission:shipping_zones.delete')->name('bulk-delete');
            Route::post('/{id}', [\App\Http\Controllers\ShippingZoneController::class, 'update'])->middleware('permission:shipping_zones.update')->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\ShippingZoneController::class, 'destroy'])->middleware('permission:shipping_zones.delete')->name('destroy');
        });
        
        // Shipping Methods
        Route::prefix('methods')->name('methods.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ShippingMethodController::class, 'index'])->middleware('permission:shipping_methods.view')->name('index');
            Route::get('/data', [\App\Http\Controllers\ShippingMethodController::class, 'getData'])->middleware('permission:shipping_methods.view')->name('data');
            Route::get('/{id}/edit', [\App\Http\Controllers\ShippingMethodController::class, 'edit'])->middleware('permission:shipping_methods.update')->name('edit');
            Route::post('/', [\App\Http\Controllers\ShippingMethodController::class, 'store'])->middleware('permission:shipping_methods.create')->name('store');
            Route::post('/bulk-delete', [\App\Http\Controllers\ShippingMethodController::class, 'bulkDelete'])->middleware('permission:shipping_methods.delete')->name('bulk-delete');
            Route::post('/{id}', [\App\Http\Controllers\ShippingMethodController::class, 'update'])->middleware('permission:shipping_methods.update')->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\ShippingMethodController::class, 'destroy'])->middleware('permission:shipping_methods.delete')->name('destroy');
        });
        
        // Shipping Rates
        Route::prefix('rates')->name('rates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ShippingRateController::class, 'index'])->middleware('permission:shipping_rates.view')->name('index');
            Route::get('/data', [\App\Http\Controllers\ShippingRateController::class, 'getData'])->middleware('permission:shipping_rates.view')->name('data');
            Route::get('/{id}/edit', [\App\Http\Controllers\ShippingRateController::class, 'edit'])->middleware('permission:shipping_rates.update')->name('edit');
            Route::post('/', [\App\Http\Controllers\ShippingRateController::class, 'store'])->middleware('permission:shipping_rates.create')->name('store');
            Route::post('/bulk-delete', [\App\Http\Controllers\ShippingRateController::class, 'bulkDelete'])->middleware('permission:shipping_rates.delete')->name('bulk-delete');
            Route::post('/{id}', [\App\Http\Controllers\ShippingRateController::class, 'update'])->middleware('permission:shipping_rates.update')->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\ShippingRateController::class, 'destroy'])->middleware('permission:shipping_rates.delete')->name('destroy');
        });
    });
    
    // Field Management Routes
    Route::get('/field-management', [\App\Http\Controllers\FieldManagementController::class, 'index'])->middleware('permission:field_management.view')->name('field-management.index');
    Route::get('/field-management/data', [\App\Http\Controllers\FieldManagementController::class, 'getData'])->middleware('permission:field_management.view')->name('field-management.data');
    Route::get('/field-management/fields', [\App\Http\Controllers\FieldManagementController::class, 'getFieldsForForm'])->middleware('permission:field_management.view')->name('field-management.fields');
    Route::get('/field-management/all-fields', [\App\Http\Controllers\FieldManagementController::class, 'getAllFieldsForPreview'])->middleware('permission:field_management.view')->name('field-management.all-fields');
    Route::post('/field-management/seed', [\App\Http\Controllers\FieldManagementController::class, 'seedInitialData'])->middleware('permission:field_management.update')->name('field-management.seed');
    Route::post('/field-management/sync-system-fields', [\App\Http\Controllers\FieldManagementController::class, 'syncSystemFields'])->middleware('permission:field_management.update')->name('field-management.sync-system-fields');
    Route::post('/field-management/{id}/toggle-status', [\App\Http\Controllers\FieldManagementController::class, 'toggleStatus'])->middleware('permission:field_management.update')->name('field-management.toggle-status');
    Route::post('/field-management/{id}/toggle-visible', [\App\Http\Controllers\FieldManagementController::class, 'toggleVisible'])->middleware('permission:field_management.update')->name('field-management.toggle-visible');
    Route::post('/field-management/{id}/toggle-required', [\App\Http\Controllers\FieldManagementController::class, 'toggleRequired'])->middleware('permission:field_management.update')->name('field-management.toggle-required');
    Route::post('/field-management/{fieldKey}/update-order', [\App\Http\Controllers\FieldManagementController::class, 'updateOrder'])->middleware('permission:field_management.update')->name('field-management.update-order');
    
    Route::get('/field-management/{id}/edit', [\App\Http\Controllers\FieldManagementController::class, 'edit'])->middleware('permission:field_management.update')->name('field-management.edit');
    Route::post('/field-management', [\App\Http\Controllers\FieldManagementController::class, 'store'])->middleware('permission:field_management.update')->name('field-management.store');
    Route::post('/field-management/{id}', [\App\Http\Controllers\FieldManagementController::class, 'update'])->middleware('permission:field_management.update')->name('field-management.update');
    Route::delete('/field-management/{id}', [\App\Http\Controllers\FieldManagementController::class, 'destroy'])->middleware('permission:field_management.update')->name('field-management.destroy');
    
    // Integrations Management Routes
    Route::get('/integrations', [\App\Http\Controllers\IntegrationController::class, 'index'])->middleware('permission:integrations.view')->name('integrations.index');
    Route::post('/integrations', [\App\Http\Controllers\IntegrationController::class, 'store'])->middleware('permission:integrations.update')->name('integrations.store');
    Route::post('/integrations/test-email', [\App\Http\Controllers\IntegrationController::class, 'testEmail'])->middleware('permission:integrations.update')->name('integrations.test-email');
    Route::get('/integrations/{id}', [\App\Http\Controllers\IntegrationController::class, 'show'])->middleware('permission:integrations.view')->name('integrations.show');
    Route::delete('/integrations/{id}', [\App\Http\Controllers\IntegrationController::class, 'destroy'])->middleware('permission:integrations.update')->name('integrations.destroy');
    
    // Customer Routes
    Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index'])->middleware('permission:customer.view')->name('customers.index');
    Route::get('/customers/fields', [\App\Http\Controllers\CustomerController::class, 'getFields'])->middleware('permission:customer.view')->name('customers.fields');
    Route::get('/customers/data', [\App\Http\Controllers\CustomerController::class, 'getData'])->middleware('permission:customer.view')->name('customers.data');
    Route::get('/customers/areas', [\App\Http\Controllers\CustomerController::class, 'getAreas'])->middleware('permission:customer.view')->name('customers.areas');
    Route::get('/customers/countries', [\App\Http\Controllers\CustomerController::class, 'getCountries'])->middleware('permission:customer.view')->name('customers.countries');
    Route::get('/customers/states', [\App\Http\Controllers\CustomerController::class, 'getStates'])->middleware('permission:customer.view')->name('customers.states');
    Route::get('/customers/cities', [\App\Http\Controllers\CustomerController::class, 'getCities'])->middleware('permission:customer.view')->name('customers.cities');
    Route::get('/customers/{id}/edit', [\App\Http\Controllers\CustomerController::class, 'edit'])->middleware('permission:customer.update')->name('customers.edit');
    Route::get('/customers/{id}/addresses', [\App\Http\Controllers\CustomerController::class, 'getAddresses'])->middleware('permission:customer.view')->name('customers.addresses');
    Route::get('/customers/{id}/orders', [\App\Http\Controllers\CustomerController::class, 'getOrders'])->middleware('permission:customer.view')->name('customers.orders');
    Route::get('/customers/{id}/cart-items', [\App\Http\Controllers\CustomerController::class, 'getCartItems'])->middleware('permission:customer.view')->name('customers.cart-items');
    Route::post('/customers', [\App\Http\Controllers\CustomerController::class, 'store'])->middleware('permission:customer.create')->name('customers.store');
    Route::post('/customers/{id}', [\App\Http\Controllers\CustomerController::class, 'update'])->middleware('permission:customer.update')->name('customers.update');
    Route::delete('/customers/{id}', [\App\Http\Controllers\CustomerController::class, 'destroy'])->middleware('permission:customer.delete')->name('customers.destroy');
    
    // Order Routes
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->middleware('permission:order.view')->name('orders.index');
    Route::get('/orders/data', [\App\Http\Controllers\OrderController::class, 'getData'])->middleware('permission:order.view')->name('orders.data');
    Route::get('/orders/counts', [\App\Http\Controllers\OrderController::class, 'getOrderCounts'])->middleware('permission:order.view')->name('orders.counts');
    Route::get('/orders/customers', [\App\Http\Controllers\OrderController::class, 'getCustomers'])->middleware('permission:order.view')->name('orders.customers');
    Route::get('/orders/customers/{id}', [\App\Http\Controllers\OrderController::class, 'getCustomerDetails'])->middleware('permission:order.view')->name('orders.customer.details');
    Route::post('/orders/calculate-shipping', [\App\Http\Controllers\OrderController::class, 'calculateShipping'])->middleware('permission:order.create')->name('orders.calculate-shipping');
    Route::get('/orders/products', [\App\Http\Controllers\OrderController::class, 'getProducts'])->middleware('permission:order.view')->name('orders.products');
    Route::get('/orders/warehouses', [\App\Http\Controllers\OrderController::class, 'getWarehouses'])->middleware('permission:order.view')->name('orders.warehouses');
    Route::get('/orders/stock-availability', [\App\Http\Controllers\OrderController::class, 'getStockAvailability'])->middleware('permission:order.view')->name('orders.stock-availability');
    Route::get('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show'])->middleware('permission:order.view')->name('orders.show');
    Route::get('/orders/{id}/edit', [\App\Http\Controllers\OrderController::class, 'edit'])->middleware('permission:order.update')->name('orders.edit');
    Route::get('/orders/{id}/invoice', [\App\Http\Controllers\OrderController::class, 'invoice'])->middleware('permission:order.view')->name('orders.invoice');
    Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store'])->middleware('permission:order.create')->name('orders.store');
    Route::post('/orders/{id}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus'])->middleware('permission:order.update')->name('orders.update-status');
    Route::match(['post', 'put'], '/orders/{id}', [\App\Http\Controllers\OrderController::class, 'update'])->middleware('permission:order.update')->name('orders.update');
    Route::delete('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'destroy'])->middleware('permission:order.delete')->name('orders.destroy');
    
    // Coupons
    Route::get('/coupons', [\App\Http\Controllers\CouponController::class, 'index'])->middleware('permission:coupons.view')->name('coupons.index');
    Route::get('/coupons/data', [\App\Http\Controllers\CouponController::class, 'getData'])->middleware('permission:coupons.view')->name('coupons.data');
    Route::get('/coupons/generate-code', [\App\Http\Controllers\CouponController::class, 'generateCode'])->middleware('permission:coupons.create')->name('coupons.generateCode');
    Route::post('/coupons/validate-code', [\App\Http\Controllers\CouponController::class, 'validateCode'])->middleware('permission:coupons.create')->name('coupons.validateCode');
    Route::post('/coupons', [\App\Http\Controllers\CouponController::class, 'store'])->middleware('permission:coupons.create')->name('coupons.store');
    Route::get('/coupons/{id}/edit', [\App\Http\Controllers\CouponController::class, 'edit'])->middleware('permission:coupons.update')->name('coupons.edit');
    Route::post('/coupons/{id}', [\App\Http\Controllers\CouponController::class, 'update'])->middleware('permission:coupons.update')->name('coupons.update');
    Route::post('/coupons/{id}/toggle-status', [\App\Http\Controllers\CouponController::class, 'toggleStatus'])->middleware('permission:coupons.update')->name('coupons.toggleStatus');
    Route::delete('/coupons/{id}', [\App\Http\Controllers\CouponController::class, 'destroy'])->middleware('permission:coupons.delete')->name('coupons.destroy');
    
    // Carts
    Route::get('/carts', [\App\Http\Controllers\CartController::class, 'index'])->middleware('permission:carts.view')->name('carts.index');
    Route::get('/carts/data', [\App\Http\Controllers\CartController::class, 'getData'])->middleware('permission:carts.view')->name('carts.data');
    Route::get('/carts/{id}', [\App\Http\Controllers\CartController::class, 'show'])->middleware('permission:carts.view')->name('carts.show');
    Route::delete('/carts/{id}', [\App\Http\Controllers\CartController::class, 'destroy'])->middleware('permission:carts.delete')->name('carts.destroy');
});
