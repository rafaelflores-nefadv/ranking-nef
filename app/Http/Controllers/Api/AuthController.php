<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Retorna o usuário autenticado
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
