<?php

use App\Http\Controllers\Admin\AdminApplicationController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInterviewController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ApplicationCategoryController;
use App\Http\Controllers\Admin\SelectedApplicantsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\DiscordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\UserPanelController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');

Route::view('/offline', 'offline')->name('offline');

Route::get('/login', fn () => redirect()->route('login.discord'))->name('login');

Route::middleware('guest')->group(function () {
    Route::get('/login/discord', [DiscordController::class, 'redirect'])
        ->middleware('throttle:6,1')
        ->name('login.discord');

    Route::get('/login/discord/callback', [DiscordController::class, 'callback'])
        ->middleware('throttle:12,1')
        ->name('login.discord.callback');
});

Route::post('/logout', [DiscordController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/notifications', [UserPanelController::class, 'notifications'])->name('user.notifications');
    Route::get('/profile', [UserPanelController::class, 'profile'])->name('user.profile');
    Route::get('/settings', [UserPanelController::class, 'settings'])->name('user.settings');
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/create', [ApplicationController::class, 'create'])->name('applications.create');
    Route::get('/applications/create/{type}', [ApplicationController::class, 'createType'])->name('applications.create.type');
    Route::post('/applications', [ApplicationController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('applications.store');
    Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/cancel', [ApplicationController::class, 'cancel'])
        ->middleware('throttle:5,1')
        ->name('applications.cancel');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:reviewer'])
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
        Route::get('/interviews', [AdminInterviewController::class, 'index'])
            ->name('interviews.index');
        Route::post('/applications/{application}/interviews', [AdminApplicationController::class, 'storeInterview'])
            ->middleware(['role:admin', 'throttle:20,1'])
            ->name('applications.interviews.store');
        Route::patch('/applications/{application}/interviews/{interview}', [AdminApplicationController::class, 'updateInterview'])
            ->middleware(['role:admin', 'throttle:20,1'])
            ->name('applications.interviews.update');
        Route::get('/selected', [SelectedApplicantsController::class, 'index'])
            ->middleware('role:admin')
            ->name('selected.index');
        Route::post('/selected/publish', [SelectedApplicantsController::class, 'publish'])
            ->middleware(['role:admin', 'throttle:10,1'])
            ->name('selected.publish');
        Route::resource('categories', ApplicationCategoryController::class)
            ->except('show')
            ->middleware('role:admin');
        Route::patch('/categories/{category}/availability', [ApplicationCategoryController::class, 'updateAvailability'])
            ->middleware(['role:admin', 'throttle:30,1'])
            ->name('categories.availability');
        Route::patch('/categories/{category}/restore', [ApplicationCategoryController::class, 'restore'])
            ->middleware(['role:admin', 'throttle:30,1'])
            ->name('categories.restore');
        Route::post('/categories/{category}/questions', [ApplicationCategoryController::class, 'storeQuestion'])
            ->middleware(['role:admin', 'throttle:30,1'])
            ->name('categories.questions.store');
        Route::patch('/categories/{category}/questions/{question}', [ApplicationCategoryController::class, 'updateQuestion'])
            ->middleware(['role:admin', 'throttle:30,1'])
            ->name('categories.questions.update');
        Route::delete('/categories/{category}/questions/{question}', [ApplicationCategoryController::class, 'destroyQuestion'])
            ->middleware(['role:admin', 'throttle:30,1'])
            ->name('categories.questions.destroy');
        Route::patch('/applications/{application}/status', [AdminApplicationController::class, 'updateStatus'])
            ->middleware(['role:admin', 'throttle:20,1'])
            ->name('applications.status');
        Route::post('/applications/{application}/notes', [AdminApplicationController::class, 'storeNote'])
            ->middleware('throttle:30,1')
            ->name('applications.notes');
        Route::get('/settings', [SettingsController::class, 'edit'])
            ->middleware('role:owner')
            ->name('settings.edit');
        Route::patch('/settings', [SettingsController::class, 'update'])
            ->middleware(['role:owner', 'throttle:10,1'])
            ->name('settings.update');
        Route::get('/users', [AdminUserController::class, 'index'])
            ->middleware('role:owner')
            ->name('users.index');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])
            ->middleware(['role:owner', 'throttle:20,1'])
            ->name('users.role');
    });
