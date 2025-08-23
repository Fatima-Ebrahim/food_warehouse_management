<?php
namespace App\Services\ReportsService;

use App\Repositories\ReportsRepository\SalesAnalysisReportRepository;

class SalesAnalysisReportService{

    public function __construct(protected SalesAnalysisReportRepository $repository)
    {}

    public function compareSales($startPeriod, $endPeriod)
    {
        $salesStart = $this->repository->getSalesTotalByPeriod($startPeriod);
        $salesEnd   = $this->repository->getSalesTotalByPeriod($endPeriod);

        $growth = $salesStart > 0
            ? (($salesEnd - $salesStart) / $salesStart) * 100
            : null;

        return [
            'start_period' => $startPeriod,
            'end_period'   => $endPeriod,
            'sales_start'  => $salesStart,
            'sales_end'    => $salesEnd,
            'growth_rate'  => $growth, // نسبة النمو %
        ];
    }

    public function getInventoryTurnover($startDate, $endDate){
        return $this->repository->getInventoryTurnover($startDate, $endDate);
    }

    public function getOffersImpactWithIncrease($startDate, $endDate){
        return $this->repository->getOffersImpactWithIncrease($startDate, $endDate);
    }


}
