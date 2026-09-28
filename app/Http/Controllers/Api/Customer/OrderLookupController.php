<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Refund\LookupOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\Refund\OrderLookupService;
use Illuminate\Http\JsonResponse;

class OrderLookupController extends Controller
{
    public function __construct(private readonly OrderLookupService $orderLookup) {}

    public function __invoke(LookupOrderRequest $request): JsonResponse
    {
        $order = $this->orderLookup->findForCustomer($request->string('email')->toString(), $request->string('order_number')->toString());

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }
}
