<?php

use App\Http\Controllers\SellerController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Rotas de autenticação (Laravel Breeze)
require __DIR__.'/auth.php';

// Rotas protegidas
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    
    // Sellers
    Route::resource('sellers', SellerController::class);
    
    // Teams
    Route::resource('teams', TeamController::class);
    
    // Users (apenas admin)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/users/{user}/reset-password', [UserController::class, 'showResetPassword'])->name('users.reset-password');
    Route::put('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password.update');
    
    // Settings (apenas admin)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications'])
        ->name('settings.notifications.update');
    Route::put('/settings/notifications/events', [SettingsController::class, 'updateNotificationEvents'])
        ->name('settings.notifications.events.update');
    Route::put('/settings/notifications/voice', [SettingsController::class, 'updateVoiceSettings'])
        ->name('settings.notifications.voice.update');
    Route::post('/settings/notifications/voice/test', [SettingsController::class, 'testVoice'])
        ->name('settings.notifications.voice.test');
    Route::put('/settings/notifications/sounds', [SettingsController::class, 'updateSoundSettings'])
        ->name('settings.notifications.sounds.update');
    Route::delete('/settings/notifications/sounds/{eventKey}', [SettingsController::class, 'removeCustomSound'])
        ->name('settings.notifications.sounds.remove');
    Route::post('/settings/score-rules', [SettingsController::class, 'storeScoreRule'])
        ->name('settings.score-rules.store');
    Route::put('/settings/score-rules/{scoreRule}', [SettingsController::class, 'updateScoreRule'])
        ->name('settings.score-rules.update');
    Route::delete('/settings/score-rules/{scoreRule}', [SettingsController::class, 'destroyScoreRule'])
        ->name('settings.score-rules.destroy');
    Route::put('/settings/general', [SettingsController::class, 'updateGeneral'])
        ->name('settings.general.update');
    Route::put('/settings/seasons-options', [SettingsController::class, 'updateSeasonOptions'])
        ->name('settings.seasons.options.update');

    // Vendas recentes para notificações
    Route::get('/scores/recent', [ScoreController::class, 'recent'])->name('scores.recent');

    // Página de notificações
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/voice/recent', [NotificationController::class, 'voiceRecent'])
        ->name('notifications.voice.recent');
});
