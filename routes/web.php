<?php

use App\Http\Controllers\BookingCancellationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\BookingController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\DestinationController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PaymentProofController;
use App\Http\Controllers\Public\TourController;
use App\Http\Controllers\Public\UserDashboardController;
use App\Http\Controllers\Public\VehicleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Marketplace Homepage
Route::get('/', HomeController::class)->name('home');

// Public Destinations Discovery
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{slug}', [DestinationController::class, 'show'])->name('destinations.show');

// Public Tour Packages Discovery
Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tours/{slug}', [TourController::class, 'show'])->name('tours.show');

// Public Vehicle Rentals Discovery
Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
Route::get('/vehicles/{slug}', [VehicleController::class, 'show'])->name('vehicles.show');

// Customer Checkout Wizard & Bookings
Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/bookings/{code}', [BookingController::class, 'show'])->name('bookings.show');
Route::post('/bookings/{code}/payment-proof', [PaymentProofController::class, 'store'])->name('bookings.payment-proof.store');
Route::get('/my-bookings', [BookingController::class, 'myBookings'])->middleware('auth')->name('bookings.my');

// Customer Dashboard
Route::get('/dashboard', [UserDashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/bookings/{code}/cancel', [BookingCancellationController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Auth Routes (Login, Register Customer, Password Reset)
require __DIR__.'/auth.php';

// Partner Routes (Onboarding, Staff, Operational Portal)
require __DIR__.'/partner.php';

// Admin Routes (KYC Verification, Moderation, Operations)
require __DIR__.'/admin.php';
