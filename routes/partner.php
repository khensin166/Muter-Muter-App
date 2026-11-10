<?php

use App\Http\Controllers\Partner\PartnerBookingController;
use App\Http\Controllers\Partner\PartnerDashboardController;
use App\Http\Controllers\Partner\PartnerProductController;
use App\Http\Controllers\Partner\PartnerRegisterController;
use App\Http\Controllers\Partner\PartnerSettlementController;
use App\Http\Controllers\Partner\PartnerStaffController;
use Illuminate\Support\Facades\Route;

// Public Partner Onboarding Routes
Route::prefix('partner')->name('partner.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('register', [PartnerRegisterController::class, 'create'])->name('register');
        Route::post('register', [PartnerRegisterController::class, 'store'])->name('register.store');
    });

    // Authenticated Partner Portal Routes
    Route::middleware(['auth', 'partner'])->group(function () {
        Route::get('/', [PartnerDashboardController::class, 'index'])->name('dashboard');

        // Staff Management (Owner only)
        Route::get('staff', [PartnerStaffController::class, 'index'])->name('staff.index');
        Route::post('staff', [PartnerStaffController::class, 'store'])->name('staff.store');
        Route::patch('staff/{id}/toggle', [PartnerStaffController::class, 'toggleStatus'])->name('staff.toggle');
        Route::delete('staff/{id}', [PartnerStaffController::class, 'destroy'])->name('staff.destroy');

        // Fleet / Product Catalog Management (Vehicles Only - Tours curated by Super Admin)
        Route::get('products', [PartnerProductController::class, 'index'])->name('products.index');
        Route::get('vehicles/create', [PartnerProductController::class, 'createVehicle'])->name('vehicles.create');
        Route::post('vehicles', [PartnerProductController::class, 'storeVehicle'])->name('vehicles.store');
        Route::patch('products/{id}/toggle', [PartnerProductController::class, 'toggleStatus'])->name('products.toggle');

        // Booking Operations & 24h SLA Handling
        Route::get('bookings', [PartnerBookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{id}', [PartnerBookingController::class, 'show'])->name('bookings.show');
        Route::post('bookings/{id}/approve', [PartnerBookingController::class, 'approve'])->name('bookings.approve');
        Route::post('bookings/{id}/reject', [PartnerBookingController::class, 'reject'])->name('bookings.reject');

        // Financial Settlement Ledger (Owner only)
        Route::get('settlements', [PartnerSettlementController::class, 'index'])->name('settlements.index');
    });
});
