<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RefundRequestResource;
use App\Services\Refund\AdminRefundQueryService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly AdminRefundQueryService $queryService) {}

    public function index(): Response
    {
        return Inertia::render('dashboard', [
            'stats' => $this->queryService->dashboardStats(),
            'recent' => RefundRequestResource::collection($this->queryService->recent())->resolve(),
        ]);
    }
}
