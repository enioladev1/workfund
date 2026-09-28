<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderIndexRequest;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Resources\AdminOrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Services\Refund\AdminOrderQueryService;
use App\Services\Refund\DTOs\CreateOrderData;
use App\Services\Refund\OrderCreationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderPageController extends Controller
{
    public function __construct(
        private readonly AdminOrderQueryService $queryService,
        private readonly OrderCreationService $orderCreation,
    ) {}

    public function index(OrderIndexRequest $request): Response
    {
        $orders = $this->queryService->paginatedList($request->filters());

        return Inertia::render('admin/orders/index', [
            'orders' => OrderResource::collection($orders->items())->resolve(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
            'filters' => $request->filters(),
        ]);
    }

    public function show(string $order): Response
    {
        $orderModel = $this->queryService->findWithRelations($order);

        abort_if($orderModel === null, 404);

        return Inertia::render('admin/orders/show', [
            'order' => (new AdminOrderDetailResource($orderModel))->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/orders/create');
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = $this->orderCreation->create(new CreateOrderData(
            customerId: $request->string('customer_id')->toString() ?: null,
            newCustomerName: $request->string('new_customer_name')->toString() ?: null,
            newCustomerEmail: $request->string('new_customer_email')->toString() ?: null,
            newCustomerPhone: $request->string('new_customer_phone')->toString() ?: null,
            orderNumber: $request->string('order_number')->toString() ?: null,
            currency: $request->string('currency')->toString(),
            isFinalSale: $request->boolean('is_final_sale'),
            orderedAt: $request->string('ordered_at')->toString(),
            deliveredAt: $request->string('delivered_at')->toString() ?: null,
            items: $request->input('items'),
        ));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Order :number created.', ['number' => $order->order_number])]);

        return to_route('admin.orders.show', $order->id);
    }
}
