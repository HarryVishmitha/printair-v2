<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AdminPricingController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\EstimateController;
use App\Http\Controllers\Admin\InvoiceDocumentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserCustomerController;
use App\Http\Controllers\Admin\WorkingGroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\CartController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\CheckoutPageController;
use App\Http\Controllers\Public\CustomerAddressController;
use App\Http\Controllers\Public\InvoicePublicController;
use App\Http\Controllers\Public\OrderPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/about-us', [HomeController::class, 'about'])->name('about');
Route::get('/terms-and-conditions', [HomeController::class, 'terms'])->name('terms');
Route::get('/privacy-policy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/terms-and-conditions', [HomeController::class, 'termsAndConditions'])->name('terms.conditions');
Route::get('/B2B/partners', [HomeController::class, 'partners'])->name('coop');
// #############################
// Public Navigation Routes
// #############################
Route::get('/products', [HomeController::class, 'products'])->name('products.index');
Route::get('/products/{product:slug}', [HomeController::class, 'productShow'])->name('products.show');
Route::post('/products/{product:slug}/price-quote', [HomeController::class, 'productPriceQuote'])
    ->middleware(['throttle:60,1'])
    ->name('products.price-quote');
Route::get('/services', [HomeController::class, 'services'])->name('services.index');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricing.index');
Route::get('/contact', [PageController::class, 'contact'])
    ->name('contact');
Route::post('/contact', [PageController::class, 'sendContact'])
    ->middleware(['throttle:contact'])
    ->name('contact.send');
Route::get('/quote', [HomeController::class, 'quote'])->name('quotes');
Route::get('/quote/create', [HomeController::class, 'quote'])->name('quotes.create');

Route::get('/checkout', [CheckoutPageController::class, 'index'])->name('checkout.page');

// ------------------------------
// Cart + Checkout (Public)
// ------------------------------
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'show'])->name('show');
    Route::post('/items', [CartController::class, 'addItem'])->name('items.add');

    Route::patch('/items/{item}', [CartController::class, 'updateItem'])
        ->middleware(['auth'])
        ->name('items.update');
    Route::delete('/items/{item}', [CartController::class, 'deleteItem'])
        ->middleware(['auth'])
        ->name('items.delete');

    // Logged-in DB cart item upload
    Route::post('/items/{item}/artwork-upload', [CartController::class, 'uploadArtwork'])
        ->middleware(['auth'])
        ->name('items.artwork.upload');
    Route::post('/items/{item}/artwork-url', [CartController::class, 'saveArtworkUrl'])
        ->middleware(['auth'])
        ->name('items.artwork.url');

    Route::post('/guest/items/artwork-url', [CartController::class, 'guestSaveArtworkUrl'])
        ->name('guest.items.artwork.url');

    Route::post('/guest/items/update', [CartController::class, 'guestUpdateItem'])
        ->name('guest.items.update');
    Route::post('/guest/items/delete', [CartController::class, 'guestDeleteItem'])
        ->name('guest.items.delete');
});

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::post('/guest/start', [CheckoutController::class, 'startGuest'])->name('guest.start');
    Route::post('/guest/verify', [CheckoutController::class, 'verifyGuestOtp'])->name('guest.verify');
    Route::post('/place-order', [CheckoutController::class, 'placeOrder'])->name('place');

    Route::get('/addresses', [CustomerAddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [CustomerAddressController::class, 'store'])->name('addresses.store');
});

Route::get('/orders/secure/{order}/{token}', [OrderPublicController::class, 'show'])
    ->name('orders.public.show');

Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('/{invoice}/view/{token}', [InvoicePublicController::class, 'show'])->name('public.show');
    Route::get('/{invoice}/download/{token}', [InvoicePublicController::class, 'download'])->name('public.download');
});

// AJAX routes
Route::get('/ajax/home/categories', [HomeController::class, 'categories'])
    ->name('ajax.home.categories');

Route::get('/ajax/home/popular-products', [HomeController::class, 'popularProducts'])
    ->name('ajax.home.popular-products');

Route::get('/ajax/products', [HomeController::class, 'productsJson'])
    ->name('ajax.products.index');

Route::get('/ajax/services', [HomeController::class, 'servicesJson'])
    ->name('ajax.services.index');

// Social Login
// #############################
use App\Http\Controllers\Auth\SocialLoginController;

Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])
    ->whereIn('provider', ['google', 'facebook'])
    ->name('social.redirect');

Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'callback'])
    ->whereIn('provider', ['google', 'facebook'])
    ->name('social.callback');

use App\Http\Controllers\Auth\ContactOnboardingController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/onboarding/contact-details', [ContactOnboardingController::class, 'show'])
        ->name('onboarding.contact');

    Route::post('/onboarding/contact-details', [ContactOnboardingController::class, 'store'])
        ->name('onboarding.contact.store');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'can:manage-working-groups'])
    ->group(function () {
        Route::get('working-groups', [WorkingGroupController::class, 'index'])
            ->name('working-groups.index');

        Route::get('working-groups/create', [WorkingGroupController::class, 'create'])
            ->name('working-groups.create');

        Route::post('working-groups', [WorkingGroupController::class, 'store'])
            ->name('working-groups.store');

        Route::get('working-groups/{workingGroup}/edit', [WorkingGroupController::class, 'edit'])
            ->name('working-groups.edit');

        Route::put('working-groups/{workingGroup}', [WorkingGroupController::class, 'update'])
            ->name('working-groups.update');

        Route::delete('working-groups/{workingGroup}', [WorkingGroupController::class, 'destroy'])
            ->name('working-groups.destroy');
    });

// Users management routes
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'can:manage-users'])
    ->group(function () {
        Route::get('users', [UserCustomerController::class, 'usersIndex'])->name('users.index');
        Route::get('users/create', [UserCustomerController::class, 'usersCreate'])->name('users.create');
        Route::post('users', [UserCustomerController::class, 'usersStore'])->name('users.store');
        Route::get('users/{user}/edit', [UserCustomerController::class, 'usersEdit'])->name('users.edit');
        Route::put('users/{user}', [UserCustomerController::class, 'usersUpdate'])->name('users.update');
        Route::delete('users/{user}', [UserCustomerController::class, 'usersDestroy'])->name('users.destroy');
    });

// Customers management routes
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'can:manage-customers'])
    ->group(function () {
        Route::get('customers', [UserCustomerController::class, 'customersIndex'])->name('customers.index');
        Route::get('customers/create', [UserCustomerController::class, 'customersCreate'])->name('customers.create');
        Route::post('customers', [UserCustomerController::class, 'customersStore'])->name('customers.store');
        Route::get('customers/{customer}/edit', [UserCustomerController::class, 'customersEdit'])->name('customers.edit');
        Route::put('customers/{customer}', [UserCustomerController::class, 'customersUpdate'])->name('customers.update');
        Route::delete('customers/{customer}', [UserCustomerController::class, 'customersDestroy'])->name('customers.destroy');
    });

// Category Management Routes
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'can:manage-categories'])
    ->group(function () {
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('categories/store', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}/update', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}/delete', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

// Admin Product Management routes
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'can:manage-products'])
    ->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products/store', [ProductController::class, 'store'])->name('products.store');

        // Edit + Update
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])
            ->name('products.edit');

        Route::put('products/{product}', [ProductController::class, 'update'])
            ->name('products.update');

        // Quick toggles (AJAX)
        Route::patch('products/{product}/status', [ProductController::class, 'updateStatus'])
            ->name('products.status');

        Route::patch('products/{product}/visibility', [ProductController::class, 'updateVisibility'])
            ->name('products.visibility');

        // Delete
        Route::delete('products/{product}', [ProductController::class, 'destroy'])
            ->name('products.destroy');

        Route::post('products/{product}/wizard/save-draft', [ProductController::class, 'wizardSaveDraft'])
            ->name('products.wizard.saveDraft');

        Route::post('products/{product}/wizard/publish', [ProductController::class, 'wizardPublish'])
            ->name('products.wizard.publish');

        // Media + future edit/update routes
        Route::prefix('products')->name('products.')->group(function () {
            Route::post('{product}/media/images', [ProductController::class, 'uploadImage'])->name('media.images.upload');
            Route::patch('{product}/media/images/reorder', [ProductController::class, 'reorderImages'])->name('media.images.reorder');
            Route::patch('{product}/media/images/{image}/featured', [ProductController::class, 'setFeaturedImage'])->name('media.images.featured');
            Route::patch('{product}/media/images/{image}', [ProductController::class, 'updateImage'])->name('media.images.update');
            Route::delete('{product}/media/images/{image}', [ProductController::class, 'deleteImage'])->name('media.images.delete');

            Route::post('{product}/media/files', [ProductController::class, 'uploadFile'])->name('media.files.upload');
            Route::patch('{product}/media/files/reorder', [ProductController::class, 'reorderFiles'])->name('media.files.reorder');
            Route::patch('{product}/media/files/{file}', [ProductController::class, 'updateFile'])->name('media.files.update');
            Route::delete('{product}/media/files/{file}', [ProductController::class, 'deleteFile'])->name('media.files.delete');
        });

        // Pricing dashboard + editor
        Route::get('/pricing', [AdminPricingController::class, 'index'])
            ->name('pricing.index');

        Route::get('/pricing/products/{product}', [AdminPricingController::class, 'show'])
            ->name('pricing.products.show');

        Route::patch('/pricing/products/{product}/working-groups/{workingGroup}/visibility', [AdminPricingController::class, 'toggleWorkingGroupVisibility'])
            ->name('pricing.products.wg.visibility');

        Route::patch('/pricing/products/{product}/working-groups/{workingGroup}/override', [AdminPricingController::class, 'toggleWorkingGroupOverride'])
            ->name('pricing.products.wg.override');

        Route::post('/pricing/products/{product}/public/ensure', [AdminPricingController::class, 'ensurePublicPricing'])
            ->name('pricing.products.public.ensure');

        Route::post('/pricing/products/{product}/working-groups/{workingGroup}/ensure', [AdminPricingController::class, 'ensureWorkingGroupPricing'])
            ->name('pricing.products.wg.ensure');

        Route::patch('/pricing/products/{product}/base', [AdminPricingController::class, 'upsertBasePricing'])
            ->name('pricing.products.base');

        Route::patch('/pricing/products/{product}/tiers', [AdminPricingController::class, 'syncTiers'])
            ->name('pricing.products.tiers.sync');

        Route::delete('/pricing/products/{product}/tiers/{tier}', [AdminPricingController::class, 'deleteTier'])
            ->name('pricing.products.tiers.delete');

        Route::patch('/pricing/products/{product}/variant-sets/{variantSet}/availability', [AdminPricingController::class, 'toggleVariantSetAvailability'])
            ->name('pricing.products.variants.availability');

        Route::patch('/pricing/products/{product}/variants/pricing', [AdminPricingController::class, 'syncVariantPricing'])
            ->name('pricing.products.variants.pricing');

        Route::patch('/pricing/products/{product}/finishings/pricing', [AdminPricingController::class, 'syncFinishingPricing'])
            ->name('pricing.products.finishings.pricing');

        Route::delete('/pricing/products/{product}/finishings/pricing/{finishingPricing}', [AdminPricingController::class, 'deleteFinishingPricing'])
            ->name('pricing.products.finishings.delete');

        Route::patch('/pricing/products/{product}/rolls/pricing', [AdminPricingController::class, 'syncRollPricing'])
            ->name('pricing.products.rolls.pricing');
    });

// Admin Roll Management routes
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'can:manage-rolls'])
    ->group(function () {
        Route::get('rolls', [\App\Http\Controllers\Admin\RollController::class, 'index'])->name('rolls.index');
        Route::get('rolls/create', [\App\Http\Controllers\Admin\RollController::class, 'create'])->name('rolls.create');
        Route::post('rolls/store', [\App\Http\Controllers\Admin\RollController::class, 'store'])->name('rolls.store');
        Route::get('rolls/{roll}/edit', [\App\Http\Controllers\Admin\RollController::class, 'edit'])->name('rolls.edit');
        Route::patch('rolls/{roll}/update', [\App\Http\Controllers\Admin\RollController::class, 'update'])->name('rolls.update');
        Route::delete('rolls/{roll}/delete', [\App\Http\Controllers\Admin\RollController::class, 'destroy'])->name('rolls.destroy');
    });

	Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'can:manage-orderFlow'])->group(function () {
	    // Estimates
	    Route::get('/estimates', [EstimateController::class, 'index'])->name('estimates.index');
	    Route::get('/estimates/create', [EstimateController::class, 'create'])->name('estimates.create');
	    Route::post('/estimates', [EstimateController::class, 'store'])->name('estimates.store');
	    Route::get('/estimates/products', [\App\Http\Controllers\Admin\EstimatePricingController::class, 'products'])->name('estimates.products');
	    Route::get('/estimates/products/{product}/rolls', [\App\Http\Controllers\Admin\EstimatePricingController::class, 'rolls'])->name('estimates.products.rolls');
	    Route::post('/estimates/quote', [\App\Http\Controllers\Admin\EstimatePricingController::class, 'quote'])->name('estimates.quote');
	    Route::get('/estimates/customer-users', [\App\Http\Controllers\Admin\EstimateCustomerController::class, 'userSearch'])->name('estimates.customer-users');
	    Route::post('/estimates/customers', [\App\Http\Controllers\Admin\EstimateCustomerController::class, 'store'])->name('estimates.customers.store');
	    Route::get('/estimates/{estimate}', [EstimateController::class, 'show'])->name('estimates.show');
	    Route::get('/estimates/{estimate}/pdf', [EstimateController::class, 'downloadPdf'])->name('estimates.pdf');
	    Route::get('/estimates/{estimate}/edit', [EstimateController::class, 'edit'])->name('estimates.edit');
	    Route::patch('/estimates/{estimate}', [EstimateController::class, 'update'])->name('estimates.update');

    Route::post('/estimates/{estimate}/recalc', [EstimateController::class, 'recalc'])->name('estimates.recalc');

    Route::post('/estimates/{estimate}/items', [EstimateController::class, 'upsertItem'])->name('estimates.items.store');
    Route::patch('/estimates/{estimate}/items/{item}', [EstimateController::class, 'upsertItem'])->name('estimates.items.update');
    Route::delete('/estimates/{estimate}/items/{item}', [EstimateController::class, 'deleteItem'])->name('estimates.items.delete');

	    Route::post('/estimates/{estimate}/send', [EstimateController::class, 'send'])->name('estimates.send');
	    Route::post('/estimates/{estimate}/resend', [EstimateController::class, 'resend'])->name('estimates.resend');
	    Route::post('/estimates/{estimate}/accept', [EstimateController::class, 'accept'])->name('estimates.accept');
	    Route::post('/estimates/{estimate}/reject', [EstimateController::class, 'reject'])->name('estimates.reject');

    Route::post('/estimates/{estimate}/shares', [EstimateController::class, 'createShare'])->name('estimates.shares.create');
    Route::post('/estimates/{estimate}/shares/{share}/revoke', [EstimateController::class, 'revokeShare'])->name('estimates.shares.revoke');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/estimates/{estimate}/convert-to-order', [OrderController::class, 'createFromEstimate'])->name('orders.from-estimate');
    Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/status', [OrderController::class, 'changeStatus'])->name('orders.status');
    Route::get('/orders/{order}/status-options', [OrderController::class, 'statusOptions'])->name('orders.status-options');

    // Billing (Invoices + Payments)
    Route::post('/orders/{order}/invoices', [BillingController::class, 'createInvoiceFromOrder'])->name('invoices.from-order');
    Route::get('/invoices', [BillingController::class, 'invoicesIndex'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [BillingController::class, 'showInvoice'])->name('invoices.show');
    Route::post('/invoices/{invoice}/issue', [BillingController::class, 'issueInvoice'])->name('invoices.issue');
    Route::post('/invoices/{invoice}/void', [BillingController::class, 'voidInvoice'])->name('invoices.void');
    Route::post('/invoices/{invoice}/payments', [BillingController::class, 'addInvoicePayment'])->name('invoices.payments.add');
    Route::get('/invoices/{invoice}/pdf', [InvoiceDocumentController::class, 'pdf'])->name('invoices.pdf');
    Route::post('/invoices/{invoice}/email', [InvoiceDocumentController::class, 'email'])->name('invoices.email');

    Route::post('/payments', [BillingController::class, 'recordPayment'])->name('payments.store');
    Route::get('/payments', [BillingController::class, 'paymentsIndex'])->name('payments.index');
    Route::get('/payments/{payment}', [BillingController::class, 'showPayment'])->name('payments.show');
    Route::post('/payments/{payment}/confirm', [BillingController::class, 'confirmPayment'])->name('payments.confirm');
    Route::post('/payments/{payment}/allocate/{invoice}', [BillingController::class, 'allocatePayment'])->name('payments.allocate');
});

// Public - Estimate share view + accept/reject by token
Route::get('/estimate/{token}', [\App\Http\Controllers\Public\EstimateShareController::class, 'show'])->name('estimates.public.show');
Route::get('/estimate/{token}/pdf', [\App\Http\Controllers\Public\EstimateShareController::class, 'downloadPdf'])
    ->name('estimates.public.pdf');
Route::post('/estimate/{token}/otp/send', [\App\Http\Controllers\Public\EstimateShareController::class, 'sendOtp'])
    ->middleware(['throttle:10,1'])
    ->name('estimates.public.otp.send');
Route::post('/estimate/{token}/otp/verify', [\App\Http\Controllers\Public\EstimateShareController::class, 'verifyOtp'])
    ->middleware(['throttle:20,1'])
    ->name('estimates.public.otp.verify');
Route::post('/estimate/{token}/accept', [\App\Http\Controllers\Public\EstimateShareController::class, 'accept'])->name('estimates.public.accept');
Route::post('/estimate/{token}/reject', [\App\Http\Controllers\Public\EstimateShareController::class, 'reject'])->name('estimates.public.reject');

Route::middleware('auth')->group(function () {
    // List pages
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/unread', [NotificationController::class, 'unread'])
        ->name('notifications.unread');

    // Mark a single notification as read (has {notification} param)
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    // Mark ALL as read (no param, matches your form route('notifications.markAsRead'))
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.markAsRead');

    // Settings
    Route::get('/notifications/settings', [NotificationController::class, 'settings'])
        ->name('notifications.settings');

    Route::post('/notifications/settings', [NotificationController::class, 'updateSettings'])
        ->name('notifications.settings.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

use App\Http\Controllers\Auth\PasswordChangeController;

Route::get('/password/force-change', [PasswordChangeController::class, 'showChangeForm'])
    ->middleware(['auth'])
    ->name('password.force-change');

Route::post('/password/force-change', [PasswordChangeController::class, 'updatePassword'])
    ->middleware(['auth']);

require __DIR__.'/auth.php';
