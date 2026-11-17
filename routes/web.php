<?php

use App\Http\Controllers\Analytics\ClinicController;
use App\Http\Controllers\Analytics\DashboardController;
use App\Http\Controllers\Analytics\LeadController;
use App\Http\Controllers\Analytics\SearchController;
use App\Http\Controllers\Analytics\SupporterController;
use App\Http\Controllers\Analytics\TrafficController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified', 'can:view-analytics'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('traffic', TrafficController::class)->name('traffic');
    Route::get('searches', SearchController::class)->name('searches');
    Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('leads/export', [LeadController::class, 'export'])->name('leads.export');
    Route::patch('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::get('clinics', ClinicController::class)->name('clinics');
    Route::get('supporters', SupporterController::class)->name('supporters');
});

require __DIR__.'/settings.php';
