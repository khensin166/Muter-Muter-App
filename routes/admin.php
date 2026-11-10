<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDestinationClusterController;
use App\Http\Controllers\Admin\AdminDestinationController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminRefundController;
use App\Http\Controllers\Admin\AdminSettlementController;
use App\Http\Controllers\Admin\AdminSlaController;
use App\Http\Controllers\Admin\AdminTourController;
use App\Http\Controllers\Admin\PartnerVerificationController;
use App\Http\Controllers\Admin\ProductModerationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Master Destinations Management (Full Enterprise CRUD & Active Toggle)
    Route::get('destinations', [AdminDestinationController::class, 'index'])->name('destinations.index');
    Route::post('destinations', [AdminDestinationController::class, 'store'])->name('destinations.store');
    Route::post('destinations/{id}', [AdminDestinationController::class, 'update'])->name('destinations.update');
    Route::delete('destinations/{id}', [AdminDestinationController::class, 'destroy'])->name('destinations.destroy');
    Route::patch('destinations/{id}/toggle', [AdminDestinationController::class, 'toggleStatus'])->name('destinations.toggle');

    // Master Destination Clusters Management (Dynamic Regional Clusters CRUD)
    Route::get('destination-clusters', [AdminDestinationClusterController::class, 'index'])->name('destination-clusters.index');
    Route::post('destination-clusters', [AdminDestinationClusterController::class, 'store'])->name('destination-clusters.store');
    Route::post('destination-clusters/{id}', [AdminDestinationClusterController::class, 'update'])->name('destination-clusters.update');
    Route::delete('destination-clusters/{id}', [AdminDestinationClusterController::class, 'destroy'])->name('destination-clusters.destroy');
    Route::patch('destination-clusters/{id}/toggle', [AdminDestinationClusterController::class, 'toggleStatus'])->name('destination-clusters.toggle');

    // Platform-Curated Tour Package Creator & Booking Dispatching
    Route::get('tours/create', [AdminTourController::class, 'create'])->name('tours.create');
    Route::post('tours', [AdminTourController::class, 'store'])->name('tours.store');
    Route::post('bookings/{id}/dispatch', [AdminTourController::class, 'dispatchBooking'])->name('bookings.dispatch');

    // Partner KYC & Verification
    Route::get('partners', [PartnerVerificationController::class, 'index'])->name('partners.index');
    Route::get('partners/{id}', [PartnerVerificationController::class, 'show'])->name('partners.show');
    Route::post('partners/{id}/approve', [PartnerVerificationController::class, 'approve'])->name('partners.approve');
    Route::post('partners/{id}/reject', [PartnerVerificationController::class, 'reject'])->name('partners.reject');
    Route::post('partners/{id}/suspend', [PartnerVerificationController::class, 'suspend'])->name('partners.suspend');

    // Product Catalog Moderation
    Route::get('products', [ProductModerationController::class, 'index'])->name('products.index');
    Route::get('products/{id}', [ProductModerationController::class, 'show'])->name('products.show');
    Route::post('products/{id}/approve', [ProductModerationController::class, 'approve'])->name('products.approve');
    Route::post('products/{id}/reject', [ProductModerationController::class, 'reject'])->name('products.reject');

    // SLA Monitoring & Manual Review Queue
    Route::get('sla-monitoring', [AdminSlaController::class, 'index'])->name('sla.index');
    Route::post('sla-monitoring/{id}/approve', [AdminSlaController::class, 'forceApprove'])->name('sla.approve');
    Route::post('sla-monitoring/{id}/cancel', [AdminSlaController::class, 'forceCancel'])->name('sla.cancel');

    // Payment Proof Verification (Down Payment)
    Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{id}', [AdminPaymentController::class, 'show'])->name('payments.show');
    Route::post('payments/{id}/approve', [AdminPaymentController::class, 'approve'])->name('payments.approve');
    Route::post('payments/{id}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');

    // Customer Refund Queue & Processing
    Route::get('refunds', [AdminRefundController::class, 'index'])->name('refunds.index');
    Route::get('refunds/{id}', [AdminRefundController::class, 'show'])->name('refunds.show');
    Route::post('refunds/{id}/process', [AdminRefundController::class, 'process'])->name('refunds.process');
    Route::post('refunds/{id}/reject', [AdminRefundController::class, 'reject'])->name('refunds.reject');

    // Vendor Settlement Ledger & Payout Execution
    Route::get('settlements', [AdminSettlementController::class, 'index'])->name('settlements.index');
    Route::post('settlements/{id}/payout', [AdminSettlementController::class, 'payout'])->name('settlements.payout');
});
