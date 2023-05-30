<?php

namespace App\Repositories\Booking\TableRepository;

use App\Models\Booking\Table;
use App\Models\Language;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TableRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Table::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter = []): LengthAwarePaginator
    {
        /** @var Table $models */
        $models = $this->model();
        $locale = data_get(Language::languagesList()->where('default', 1)->first(), 'locale');

        return $models
            ->filter($filter)
            ->with([
                'shopSection' => fn($query) => $query
                    ->when(data_get($filter, 'shop_id'), function ($query, $shopId) {
                        $query->where('shop_id', $shopId);
                    }),
                'shopSection.translation' => fn($q) => $q->where('locale', $this->language)->orWhere('locale', $locale),
            ])
            ->orderBy(data_get($filter, 'column', 'id'), data_get($filter, 'sort', 'desc'))
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param Table $model
     * @return Table|null
     */
    public function show(Table $model): Table|null
    {
        $locale = data_get(Language::languagesList()->where('default', 1)->first(), 'locale');

        return $model->loadMissing([
            'shopSection.translation'       => fn($q) => $q->where('locale', $this->language)->orWhere('locale', $locale),
            'shopSection.shop.translation'  => fn($q) => $q->where('locale', $this->language)->orWhere('locale', $locale)
        ]);
    }

    /**
     * @param array $filter
     * @return array
     */
    public function disableDates(array $filter = []): array
    {
        $dateFrom = data_get($filter, 'date_from');
        $dateTo   = data_get($filter, 'date_to');

        $model = Table::with('users')
            ->whereHas('shopSection', fn($q) => $q->when(data_get($filter, 'shop_id'),
                fn($b) => $b->where('shop_id', data_get($filter, 'shop_id'))
            ))
            ->where('id', data_get($filter, 'id'))
            ->whereHas('users', function ($q) use ($dateFrom, $dateTo) {
                $q
                    ->where('start_date', '>=', $dateFrom)
                    ->when($dateTo, fn($b) => $b->where('end_date', '<=', $dateTo));
            })
        ->first();

        $bookedDays = [];

        if (empty($model)) {
            return $bookedDays;
        }

        /** @var Table $model */
        foreach ($model->users as $user) {

            if (
                !empty($dateTo) &&
                date('Y-m-d', strtotime($user->start_date)) >= $dateFrom &&
                date('Y-m-d', strtotime($user->start_date)) <= $dateTo
            ) {
                $bookedDays[] = [
                    'start_date'    => $user->start_date,
                    'end_date'      => $user->end_date,
                ];
                continue;
            }

            if (date('Y-m-d', strtotime($user->start_date)) !== $dateFrom) {
                continue;
            }

            $bookedDays[] = [
                'start_date'    => $user->start_date,
                'end_date'      => $user->end_date,
            ];

        }

        return $bookedDays;
    }
}
