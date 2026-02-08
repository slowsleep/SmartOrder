<?php

namespace App\Http\Controllers\Api\Admin\Statistics;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Order;

abstract class BaseStatisticsController
{
    /**
     * Получить дату начала периода
     * @param string $period
     * @param Request $request
     * @return Carbon
     */
    protected function getStartDate(string $period, Request $request): Carbon
    {
        return match ($period) {
            'day' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            'custom' => Carbon::createFromFormat('Y-m-d', $request->query('startDate'))->startOfDay(),
            'all' => Carbon::parse(Order::min('created_at') ?? Carbon::today())->startOfDay(),
            default => Carbon::today(),
        };
    }

    /**
     * Получить дату конца периода
     * @param Request $request
     * @return Carbon
     */
    protected function getEndDate(Request $request): Carbon
    {
        $period = $request->query('period', 'day');

        if ($period === 'all') {
            $maxCreated = Order::max('created_at');
            return $maxCreated ? Carbon::parse($maxCreated)->endOfDay() : Carbon::now();
        }

        return match ($period) {
            'day' => Carbon::now(),
            'week' => Carbon::now(),
            'month' => Carbon::now(),
            'year' => Carbon::now(),
            'custom' => Carbon::createFromFormat('Y-m-d', $request->query('endDate'))->endOfDay(),
            default => Carbon::now(),
        };
    }

    /**
     * Формат даты для группировки PostgreSQL
     * @param string $period
     * @return string
     */
    protected function getDateFormat(string $period): string
    {
        return match ($period) {
            'day' => 'YYYY-MM-DD HH24',   // по часам
            'week' => 'YYYY-MM-DD',       // по дням
            'month' => 'YYYY-MM-DD',      // по дням
            'year' => 'YYYY-MM',          // по месяцам
            'custom' => 'YYYY-MM-DD',     // по дням для кастомного периода
            default => 'YYYY-MM-DD',
        };
    }

    protected function getPeriodLabel(string $period, Carbon $startDate, Carbon $endDate): string
    {
        return match ($period) {
            'all' => 'За все время: ' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y'),
            'day' => 'За день: ' . $startDate->format('d.m.Y'),
            'week' => 'За неделю: ' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y'),
            'month' => 'За месяц: ' . $startDate->format('F Y'),
            'year' => 'За год: ' . $startDate->format('Y'),
            'custom' => 'За период: ' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y'),
            default => 'За все время: ' . $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y'),
        };
    }
}
