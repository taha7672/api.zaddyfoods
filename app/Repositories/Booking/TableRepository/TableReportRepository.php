<?php

namespace App\Repositories\Booking\TableRepository;

use App\Models\Booking\Table;
use App\Models\Order;
use App\Repositories\CoreRepository;
use DB;

class TableReportRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Table::class;
    }

    /**
     * @param int|null $shopId
     * @return array
     */
    public function bookings(?int $shopId = null): array
    {
        $statistic = [
            "available" => 0,
            "booked"    => 0,
            "occupied"  => 0,
        ];

        $shopSections = DB::table("shop_sections")
            ->select(["id", "shop_id"])
            ->when($shopId, fn($q) => $q->where("shop_id", $shopId))
            ->get();

        foreach ($shopSections as $shopSection) {


            $tables = DB::table("tables")
                ->select(["id", "shop_section_id"])
                ->where("shop_section_id", $shopSection->id)
                ->get();

            foreach ($tables as $table) {

                $booked = DB::table("user_bookings")
                    ->where("end_date", ">=", now()->format('Y-m-d 00:00:01'))
                    ->where("table_id", $table->id)
                    ->select([
                        "start_date",
                        "user_id",
                        DB::raw("count(id) as booked"),
                    ])
                    ->groupBy(["user_id", "start_date"])
                    ->get();

                $bookedCount = (int)$booked->sum("booked");

                $occupied = DB::table('orders')
                    ->select(['id'])
                    ->where('table_id', $table->id)
                    ->where('status', Order::STATUS_NEW)
                    ->count(['id']);

                $statistic["booked"]    += $bookedCount;
                $statistic["occupied"]  += $occupied;

                if ($bookedCount === 0) {
                    $statistic["available"] += 1;
                }

            }
        }

        return $statistic;
    }
}
