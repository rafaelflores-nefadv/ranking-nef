<?php

use App\Http\Controllers\Api\WebhookOccurrenceController;
use Illuminate\Support\Facades\Route;

// Webhook público (sem middleware de auth web); autenticação é via Bearer Token no controller
Route::post('/webhook/occurrences', [WebhookOccurrenceController::class, 'store']);
