<?php

namespace App\Services\Booking\BookingService;

use App\Helpers\ResponseError;
use App\Models\Booking\UserBooking;
use App\Services\CoreService;
use Exception;
use Throwable;

class UserBookingService extends CoreService
{
    protected function getModelClass(): string
    {
        return UserBooking::class;
    }

    public function create(array $data): array
    {
        try {
            $userBooking = UserBooking::where([
                ['table_id', data_get($data, 'table_id')],
                ['start_date', '>=', data_get($data, 'start_date')],
                ['end_date', '<=', data_get($data, 'end_date')],
                ['status', UserBooking::NEW],
            ])
                ->first();

            if ($userBooking) {
                throw new Exception(__('errors.' . ResponseError::TABLE_BOOKING_EXISTS, [
                    'start_date' => data_get($data, 'start_date'),
                    'end_date'   => data_get($data, 'end_date'),
                ], $this->language));
            }

            return [
                'status'    => true,
                'code'      => ResponseError::NO_ERROR,
                'data'      => $this->model()->create($data),
            ];
        } catch (Throwable $e) {
            $this->error($e);

            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_501,
                'message'   => $e->getMessage()
            ];
        }
    }

    public function update(UserBooking $model, array $data): array
    {
        try {
            $userBooking = UserBooking::where([
                ['id', '!=', $model->id],
                ['table_id', data_get($data, 'table_id')],
                ['start_date', '>=', data_get($data, 'start_date')],
                ['end_date', '<=', data_get($data, 'end_date')],
                ['status', UserBooking::NEW],
            ])
                ->exists();

            if ($userBooking) {
                throw new Exception(__('errors.' . ResponseError::TABLE_BOOKING_EXISTS, [
                    'start_date' => data_get($data, 'start_date'),
                    'end_date'   => data_get($data, 'end_date'),
                ], $this->language));
            }

            $model->update($data);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $model,
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_502,
                'message'   => $e->getMessage()
            ];
        }
    }

    public function statusUpdate(int $id, array $data): array
    {
        try {
            $model = UserBooking::find($id);

            if (!$model) {
                return [
                    'status' => false,
                    'code'   => ResponseError::ERROR_404,
                    'data'   => $model,
                ];
            }

            $userBooking = UserBooking::where([
                ['id', '!=', $model->id],
                ['table_id', $model->table_id],
                ['start_date', '>=', $model->start_date],
                ['end_date', '<=', $model->end_date],
                ['status', UserBooking::ACCEPTED],
            ])
                ->exists();

            if ($userBooking) {
                throw new Exception(__('errors.' . ResponseError::TABLE_BOOKING_EXISTS, [
                    'start_date' => $model->start_date,
                    'end_date'   => $model->end_date,
                ], $this->language));
            }

            $model->update($data);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $model,
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_502,
                'message'   => $e->getMessage()
            ];
        }
    }

    public function delete(?array $ids = [], ?int $userId = null): array
    {
        $models = UserBooking::whereIn('id', is_array($ids) ? $ids : [])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->get();

        $errorIds = [];

        foreach ($models as $model) {
            /** @var UserBooking $model */
            try {
                $model->delete();
            } catch (Throwable $e) {
                $this->error($e);
                $errorIds[] = $model->id;
            }
        }

        if (count($errorIds) === 0) {
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }

        return [
            'status'  => false,
            'code'    => ResponseError::ERROR_503,
            'message' => __('errors.' . ResponseError::CANT_DELETE_IDS, ['ids' => implode(', ', $errorIds)], $this->language)
        ];
    }

}
