<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\WorkingGroupController;
use App\Http\Controllers\Admin\UserCustomerController;
use App\Http\Controllers\Admin\CategoryController;
use \App\Http\Controllers\Admin\ProductController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/about-us', [HomeController::class, 'about'])->name('about');
Route::get('/terms-and-conditions', [HomeController::class, 'terms'])->name('terms');
Route::get('/privacy-policy', [HomeController::class, 'privacy'])->name('privacy');

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

//Category Management Routes
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

//Admin Product Management routes
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
    });


//Admin Roll Management routes
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
