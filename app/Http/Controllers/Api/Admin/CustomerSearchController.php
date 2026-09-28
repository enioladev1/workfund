<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Services\Refund\AdminCustomerQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerSearchController extends Controller
{
    public function __construct(private readonly AdminCustomerQueryService $queryService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->string('q')->toString();

        $customers = $query !== '' ? $this->queryService->search($query) : collect();

        return response()->json([
            'success' => true,
            'data' => CustomerResource::collection($customers),
        ]);
    }
}
