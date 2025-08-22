<?php
namespace App\Repositories\AdminRepository;

use App\Models\Order;
use Carbon\Carbon;

class ReportRepository
{
    public function getSalesReport(string $type, ?string $from = null, ?string $to = null)
    {
        $query = Order::query()->whereIn('status',['paid','partially_paid']); // أو delivered حسب شغلك

        switch ($type) {
            case 'daily':
                $query->whereDate('created_at', Carbon::today());
                break;

            case 'monthly':
                $query->whereYear('created_at', Carbon::today()->year)
                    ->whereMonth('created_at', Carbon::today()->month);
                break;

            case 'yearly':
                $query->whereYear('created_at', Carbon::today()->year);
                break;

            case 'last10years':
                $query->where('created_at', '>=', Carbon::today()->subYears(10));
                break;

            case 'custom':
                $query->whereBetween('created_at', [Carbon::parse($from), Carbon::parse($to)]);
                break;
        }

        return $query->get();
    }
}
