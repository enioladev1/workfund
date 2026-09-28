<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerIndexRequest;
use App\Http\Resources\AdminCustomerResource;
use App\Services\Refund\AdminCustomerQueryService;
use Inertia\Inertia;
use Inertia\Response;

class CustomerPageController extends Controller
{
    public function __construct(private readonly AdminCustomerQueryService $queryService) {}

    public function index(CustomerIndexRequest $request): Response
    {
        $customers = $this->queryService->paginatedList($request->filters());

        return Inertia::render('admin/customers/index', [
            'customers' => AdminCustomerResource::collection($customers->items())->resolve(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'total' => $customers->total(),
            ],
            'filters' => $request->filters(),
        ]);
    }
}
