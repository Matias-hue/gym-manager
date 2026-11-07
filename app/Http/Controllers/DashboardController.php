<?php

namespace App\Http\Controllers;

use App\Services\DashboardStats;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardStats $stats): View
    {
        $user = $request->user();

        abort_unless($user->gym_id, 403, 'Tu usuario no pertenece a ningún gimnasio.');

        return view('dashboard', $stats->handle($user));
    }
}