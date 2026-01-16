<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado. Apenas administradores podem acessar esta página.');
        }

        return view('dashboard.analytics');
    }
}
