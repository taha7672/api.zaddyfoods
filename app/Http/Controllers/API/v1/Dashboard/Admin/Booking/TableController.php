<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin\Booking;

use App\Helpers\ResponseError;
use App\Http\Controllers\API\v1\Dashboard\Admin\AdminBaseController;
use App\Http\Requests\Booking\Table\StoreRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\Booking\TableResource;
use App\Models\Booking\Table;
use App\Repositories\Booking\TableRepository\TableReportRepository;
use App\Repositories\Booking\TableRepository\TableRepository;
use App\Services\Booking\TableService\TableService;
use Illuminate\Http\JsonResponse;

class TableController extends AdminBaseController
{
    public function __construct(
        private TableService $service,
        private TableRepository $repository,
        private TableReportRepository $reportRepository,
    )
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function index(FilterParamsRequest $request): JsonResponse
    {
        $model = $this->repository->paginate($request->all());

        $statistic  = $this->reportRepository->bookings();

        return $this->successResponse(__('errors.' . ResponseError::SUCCESS, locale: $this->language), [
            'statistic' => $statistic,
            'tables'    => TableResource::collection($model),
            'meta'      => [
                'current_page'  => $model->currentPage(),
                'per_page'      => $model->perPage(),
                'last_page'     => $model->lastPage(),
                'total'         => $model->total(),
                'from'          => $model->currentPage(),
                'to'            => $model->lastPage(),
            ],
            'links'             => $model->links(),
        ]);
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

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            TableResource::make(data_get($result, 'data'))
        );
    }

    /**
     * @param Table $table
     * @return JsonResponse
     */
    public function show(Table $table): JsonResponse
    {
        $result = $this->repository->show($table);

        return $this->successResponse(
            __('errors.' . ResponseError::SUCCESS, locale: $this->language),
            TableResource::make($result)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Table $table
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function update(Table $table, StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->service->update($table, $validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            TableResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @param FilterParamsRequest $request
     * @return array
     */
    public function disableDates(int $id, FilterParamsRequest $request): array
    {
        $filter = $request->merge(['id' => $id])->all();

        return $this->repository->disableDates($filter);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $result = $this->service->delete($request->input('ids', []));

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse([
                'code'      => ResponseError::ERROR_404,
                'message'   => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language)
        );
    }

    /**
     * @return JsonResponse
     */
    public function dropAll(): JsonResponse
    {
        $this->service->dropAll();

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language)
        );
    }

    /**
     * @return JsonResponse
     */
    public function truncate(): JsonResponse
    {
        $this->service->truncate();

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language)
        );
    }

    /**
     * @return JsonResponse
     */
    public function restoreAll(): JsonResponse
    {
        $this->service->restoreAll();

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language)
        );
    }

}
