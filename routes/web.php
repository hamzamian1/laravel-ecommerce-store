<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HeroBannerController;
use App\Http\Controllers\SubcategoryImageController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\NewsletterController;

Route::get('/', [ProductController::class, 'storeFront'])->name('home');
Route::get('/shop/{category?}/{subcategory?}', [ProductController::class, 'storeFront'])->name('shop');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');
Route::post('/product/{product}/review', [ProductController::class, 'submitReview'])->name('product.review');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/buy/{product}', [CartController::class, 'buyNow'])->name('cart.buy');
Route::post('/cart/update/{uniqueId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{uniqueId}', [CartController::class, 'remove'])->name('cart.remove');

Route::post('/wishlist/details', [ProductController::class, 'getWishlistDetails'])->name('wishlist.details');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'process'])->middleware('throttle:10,1')->name('checkout.process');

// Stripe PaymentIntent (AJAX)
Route::post('/checkout/create-payment-intent', [CheckoutController::class, 'createPaymentIntent'])->middleware('throttle:10,1')->name('checkout.paymentIntent');
// Stripe webhook (CSRF excluded in bootstrap/app.php)
Route::post('/stripe/webhook', [CheckoutController::class, 'stripeWebhook'])->name('stripe.webhook');

// Order tracking (requires authentication — no guest access)
Route::middleware('auth')->group(function () {
    Route::get('/track-order', [OrderController::class, 'trackOrder'])->name('order.track');
    Route::post('/track-order', [OrderController::class, 'trackOrder'])->middleware('throttle:15,1')->name('order.track.search');
    Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('order.history');
    Route::post('/order/{order}/confirm-delivery', [OrderController::class, 'confirmDelivery'])->name('order.confirm');
});
Route::get('/order/success/{order}', [OrderController::class, 'success'])->name('order.success');
Route::get('/payment/process/{order}', [OrderController::class, 'processPayment'])->name('payment.process');

// Custom Secure Admin Login Routes
Route::get('/oxybliss-admin-panel', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/oxybliss-admin-panel', [AdminAuthController::class, 'store']);

// Admin dashboard — requires auth + admin role
Route::middleware(['auth', 'admin'])->prefix('oxybliss-admin-panel')->group(function () {
    Route::get('/dashboard', [ProductController::class, 'index'])->name('dashboard');
});

// Admin-only routes — protected by auth + admin middleware
Route::middleware(['auth', 'admin'])->prefix('oxybliss-admin-panel')->group(function () {
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('products.bulkDestroy');
    Route::post('/products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('products.toggleFeatured');
    Route::post('/products/{product}/toggle-top5', [ProductController::class, 'toggleTop5'])->name('products.toggleTop5');
    
    // Order management (admin only)
    Route::get('/orders/pending-count', [OrderController::class, 'getPendingCount'])->name('orders.pendingCount');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    
    // Hero Banner management
    Route::post('/hero-banners', [HeroBannerController::class, 'store'])->name('hero.banners.store');
    Route::delete('/hero-banners/{heroBanner}', [HeroBannerController::class, 'destroy'])->name('hero.banners.destroy');
    Route::post('/hero-banners/{heroBanner}/toggle', [HeroBannerController::class, 'toggleActive'])->name('hero.banners.toggle');

    // Subcategory images management
    Route::post('/subcategory-images/{subcategory}', [SubcategoryImageController::class, 'store'])->name('subcategory.images.store');
    Route::delete('/subcategory-images/{subcategory}', [SubcategoryImageController::class, 'destroy'])->name('subcategory.images.destroy');

    // Newsletter subscriber management (admin)
    Route::get('/subscribers', [NewsletterController::class, 'index'])->name('newsletter.subscribers');
    Route::delete('/subscribers/{id}', [NewsletterController::class, 'destroy'])->name('newsletter.subscribers.destroy');
});

// Newsletter subscribe (public)
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('throttle:10,1')->name('newsletter.subscribe');

Route::get('/clear-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return "Application cache, views, and routes have been successfully cleared! You can now log into the admin panel.";
});

// Authenticated user routes (profile management)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::view('/account', 'account')->name('account');
});

require __DIR__.'/auth.php';

// Policy Routes
Route::get('/privacy-policy', [\App\Http\Controllers\LegalController::class, 'privacy'])->name('policy.privacy');
Route::get('/refund-policy', [\App\Http\Controllers\LegalController::class, 'refund'])->name('policy.refund');
Route::get('/terms', [\App\Http\Controllers\LegalController::class, 'terms'])->name('policy.terms');
Route::get('/shipping-policy', [\App\Http\Controllers\LegalController::class, 'shipping'])->name('policy.shipping');

Route::get('/run-live-migration', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return "Migration and Cache Clear completed successfully!";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
