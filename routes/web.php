<?php

use App\Http\Controllers\SellerController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardAnalyticsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\Goals\GoalController;
use App\Http\Controllers\Reports\RankingGeneralController;
use App\Http\Controllers\Reports\RankingTeamController;
use App\Http\Controllers\Reports\ScoreEvolutionController;
use App\Http\Controllers\Reports\OccurrencesController;
use App\Http\Controllers\Reports\GamificationController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\Admin\MonitorController as AdminMonitorController;
use App\Http\Controllers\Settings\ApiIntegrationController;
use Illuminate\Support\Facades\Route;

// Rotas de autenticação (Laravel Breeze)
require __DIR__.'/auth.php';

// Rota para obter token CSRF atualizado (para evitar erro 419)
Route::get('/csrf-token', function() {
    return response()->json(['token' => csrf_token()]);
})->middleware('web');

// Rotas públicas de monitor
Route::get('/monitor/{slug}', [MonitorController::class, 'show'])->name('monitor.show');
Route::get('/monitor/{slug}/data', [MonitorController::class, 'data'])->name('monitor.data');
Route::get('/monitor/{slug}/voice', [MonitorController::class, 'voiceText'])->name('monitor.voice');
Route::get('/monitor/{slug}/voice/status', [MonitorController::class, 'voiceStatus'])->name('monitor.voice.status');

// Rotas públicas para notificações do monitor (sem autenticação)
Route::get('/scores/recent', [ScoreController::class, 'recent'])->name('scores.recent');
Route::get('/notifications/voice/recent', [NotificationController::class, 'voiceRecent'])
    ->name('notifications.voice.recent');

// Rotas protegidas
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    
    // Dashboard Analytics (apenas admin)
    Route::get('/dashboard/analytics', [DashboardAnalyticsController::class, 'index'])->name('dashboard.analytics');
    
    // Sellers
    Route::get('/sellers/import', [SellerController::class, 'import'])->name('sellers.import');
    Route::post('/sellers/import', [SellerController::class, 'processImport'])->name('sellers.import.process');
    Route::resource('sellers', SellerController::class);
    
    // Teams
    Route::resource('teams', TeamController::class);
    
    // Goals
    Route::resource('goals', GoalController::class);
    Route::post('/goals/{goal}/duplicate', [GoalController::class, 'duplicate'])->name('goals.duplicate');
    
    // Users (apenas admin)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
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
    Route::put('/settings/permissions', [SettingsController::class, 'updatePermissions'])
        ->name('settings.permissions.update');
    Route::put('/settings/themes', [SettingsController::class, 'updateTheme'])
        ->name('settings.themes.update');
    Route::get('/settings/themes/{theme}/preview', [SettingsController::class, 'previewTheme'])
        ->name('settings.themes.preview');
    
    // API Integrations (apenas admin)
    Route::prefix('settings/api')->name('settings.api.')->group(function () {
        Route::get('/', [ApiIntegrationController::class, 'index'])->name('index');
        Route::get('/create', [ApiIntegrationController::class, 'create'])->name('create');
        Route::post('/', [ApiIntegrationController::class, 'store'])->name('store');
        Route::get('/{apiIntegration}/edit', [ApiIntegrationController::class, 'edit'])->name('edit');
        Route::put('/{apiIntegration}', [ApiIntegrationController::class, 'update'])->name('update');
        Route::delete('/{apiIntegration}', [ApiIntegrationController::class, 'destroy'])->name('destroy');
        Route::post('/{apiIntegration}/tokens/generate', [ApiIntegrationController::class, 'generateToken'])->name('tokens.generate');
        Route::patch('/{apiIntegration}/tokens/{apiToken}/toggle-status', [ApiIntegrationController::class, 'toggleTokenStatus'])->name('tokens.toggle-status');
        Route::post('/{apiIntegration}/tokens/{apiToken}/regenerate', [ApiIntegrationController::class, 'regenerateToken'])->name('tokens.regenerate');
    });

    // Setores (apenas admin)
    Route::get('/sectors', [SectorController::class, 'index'])->name('sectors.index');
    Route::get('/sectors/create', [SectorController::class, 'create'])->name('sectors.create');
    Route::post('/sectors', [SectorController::class, 'store'])->name('sectors.store');
    Route::get('/sectors/{sector}/edit', [SectorController::class, 'edit'])->name('sectors.edit');
    Route::put('/sectors/{sector}', [SectorController::class, 'update'])->name('sectors.update');
    Route::patch('/sectors/{sector}/toggle-status', [SectorController::class, 'toggleStatus'])->name('sectors.toggle-status');

    // Página de notificações (requer autenticação)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // Relatórios
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/ranking-general', [RankingGeneralController::class, 'index'])->name('ranking-general');
        Route::get('/ranking-team', [RankingTeamController::class, 'index'])->name('ranking-team');
        Route::get('/score-evolution', [ScoreEvolutionController::class, 'index'])->name('score-evolution');
        Route::get('/occurrences', [OccurrencesController::class, 'index'])->name('occurrences');
        Route::get('/gamification', [GamificationController::class, 'index'])->name('gamification');
    });

    // Monitores (apenas admin)
    Route::prefix('admin/monitors')->name('admin.monitors.')->group(function () {
        Route::get('/', [AdminMonitorController::class, 'index'])->name('index');
        Route::get('/create', [AdminMonitorController::class, 'create'])->name('create');
        Route::get('/teams', [AdminMonitorController::class, 'teamsForSectors'])->name('teams-for-sectors');
        Route::post('/', [AdminMonitorController::class, 'store'])->name('store');
        Route::get('/{monitor}', [AdminMonitorController::class, 'show'])->name('show');
        Route::get('/{monitor}/edit', [AdminMonitorController::class, 'edit'])->name('edit');
        Route::put('/{monitor}', [AdminMonitorController::class, 'update'])->name('update');
        Route::delete('/{monitor}', [AdminMonitorController::class, 'destroy'])->name('destroy');
        Route::patch('/{monitor}/toggle-status', [AdminMonitorController::class, 'toggleStatus'])->name('toggle-status');
    });
});
