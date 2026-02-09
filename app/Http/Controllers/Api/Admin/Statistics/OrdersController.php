<?php

namespace App\Http\Controllers\Api\Admin\Statistics;

use App\Http\Controllers\Api\Admin\Statistics\BaseStatisticsController;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersController extends BaseStatisticsController
{
    use ApiResponse;

    public function index(Request $request)
    {
        $period = $request->query('period', 'all');
        $startDate = $this->getStartDate($period, $request);
        $endDate = $this->getEndDate($request);

        $totalStats = $this->totalStats($startDate, $endDate);

        return $this->success([
            'total_orders' => $totalStats['total_orders'],
            'completed' => $totalStats['completed_orders'],
            'cancelled' => $totalStats['cancelled_orders'],
            'in_progress' => $totalStats['in_progress_orders'],
            'not_paid' => $totalStats['not_paid'],
            'period' => $this->getPeriodLabel($period, $startDate, $endDate),
            'date_range' => [
                'start' => $startDate->format('Y-m-d H:i:s'),
                'end' => $endDate->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Общая статистика
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function totalStats($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders"),
                DB::raw("SUM(CASE WHEN status IN ('preparing', 'confirmed') THEN 1 ELSE 0 END) as in_progress_orders"),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as not_paid")
            )
            ->first()
            ->toArray();
    }

}
