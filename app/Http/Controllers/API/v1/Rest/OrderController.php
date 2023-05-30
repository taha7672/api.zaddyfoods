<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Requests\Order\StoreRequest;
use App\Repositories\OrderRepository\OrderRepository;
use App\Services\OrderService\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends RestBaseController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderService $orderService
    )
    {
        parent::__construct();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->orderService->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            $this->orderRepository->reDataOrder(data_get($result, 'data'))
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepository->orderById($id);

        return $this->successResponse(ResponseError::NO_ERROR, $this->orderRepository->reDataOrder($order));
    }

}
