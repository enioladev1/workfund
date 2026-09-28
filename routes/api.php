<?php

use App\Http\Controllers\Api\Customer\OrderLookupController;
use App\Http\Controllers\Api\Customer\RefundController;
use Illuminate\Support\Facades\Route;

/**
 * Public, unauthenticated, customer-facing endpoints. Every one of these is
 * rate limited and scopes any lookup to the email the customer provides;
 * nothing here trusts a bare ID from the client.
 */
Route::post('/orders/lookup', OrderLookupController::class)->middleware('throttle:order-lookup');

Route::post('/refunds', [RefundController::class, 'store'])->middleware('throttle:refund-submit');
Route::get('/refunds/{refund}', [RefundController::class, 'show'])->middleware('throttle:refund-status');
