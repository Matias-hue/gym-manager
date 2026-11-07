<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DueController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ReportController;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('members', MemberController::class)->except('destroy');

    // Cobrar: formulario y guardado, con el socio ya elegido
    Route::get('members/{member}/collect', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('members/{member}/collect', [PaymentController::class, 'store'])->name('payments.store');

    // Prórroga para un socio que avisó que paga más tarde
    Route::post('memberships/{membership}/extend', [MembershipController::class, 'extend'])->name('memberships.extend');

    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/search', [AttendanceController::class, 'search'])->name('attendances.search')->middleware('throttle:120,1');
    Route::post('attendances', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::delete('attendances/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');
    Route::get('dues', [DueController::class, 'index'])->name('dues.index');
    Route::post('dues/{member}/notify', [DueController::class, 'notify'])->name('dues.notify');

    // Solo el dueño
    Route::middleware('can:owner')->group(function () {
        Route::resource('plans', PlanController::class)->except('show');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });
});

require __DIR__.'/auth.php';