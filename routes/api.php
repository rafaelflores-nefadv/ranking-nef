<?php

use App\Http\Controllers\Api\WebhookOccurrenceController;
use Illuminate\Support\Facades\Route;

// Webhook público (sem autenticação)
Route::post('/webhook/occurrences', [WebhookOccurrenceController::class, 'store']);
