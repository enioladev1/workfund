<?php

use App\Http\Controllers\Admin\CustomerPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderPageController;
use App\Http\Controllers\Admin\RefundPageController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Api\Admin\CustomerSearchController;
use App\Http\Controllers\Api\Admin\RefundController as AdminApiRefundController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'refunds/request')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('admin/refunds', [RefundPageController::class, 'index'])->name('admin.refunds.index');
    Route::get('admin/refunds/{refund}', [RefundPageController::class, 'show'])->name('admin.refunds.show');

    Route::get('admin/orders', [OrderPageController::class, 'index'])->name('admin.orders.index');
    Route::get('admin/orders/create', [OrderPageController::class, 'create'])->name('admin.orders.create');
    Route::post('admin/orders', [OrderPageController::class, 'store'])->name('admin.orders.store');
    Route::get('admin/orders/{order}', [OrderPageController::class, 'show'])->name('admin.orders.show');

    Route::get('admin/customers', [CustomerPageController::class, 'index'])->name('admin.customers.index');

    Route::get('admin/settings', [AdminSettingsController::class, 'edit'])->name('admin.settings.edit');
    Route::put('admin/settings', [AdminSettingsController::class, 'update'])->name('admin.settings.update');

    Route::middleware('throttle:admin-api')->prefix('api/admin')->group(function () {
        Route::get('refunds', [AdminApiRefundController::class, 'index'])->name('api.admin.refunds.index');
        Route::get('refunds/{refund}', [AdminApiRefundController::class, 'show'])->name('api.admin.refunds.show');
        Route::get('refunds/{refund}/audit', [AdminApiRefundController::class, 'audit'])->name('api.admin.refunds.audit');
        Route::patch('refunds/{refund}/resolve', [AdminApiRefundController::class, 'resolve'])->name('api.admin.refunds.resolve');

        Route::get('customers/search', CustomerSearchController::class)->name('api.admin.customers.search');
    });
});

require __DIR__.'/settings.php';
