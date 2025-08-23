<?php
namespace App\Services\AdminServices;


use App\Repositories\AdminRepository\ReportRepository;

class ReportService
{


    public function __construct(protected ReportRepository $reportRepository)
    {}

    public function generateReport(string $type, ?string $from = null, ?string $to = null): array
    {
        $orders = $this->reportRepository->getSalesReport($type, $from, $to);
        return [
            'total_orders' => $orders->count(),
            'total_sales'  => $orders->sum('final_price'),
            'orders'       => $orders
        ];
    }
}
