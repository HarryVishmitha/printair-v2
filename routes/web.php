<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\WorkingGroupController;

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

require __DIR__.'/auth.php';
