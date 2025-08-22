<?php
namespace App\Services\ReportsService;

use App\Repositories\ReportsRepository\FinancialReportsRepository;

class FinancialReportService{
    public function __construct(protected FinancialReportsRepository $financialReportsRepository)
    {
    }

    public function netProfitPerItem($from , $to)
    {
        return $this->financialReportsRepository->getNetProfitPerItem($from??null,$to??null);
    }

    public function accountsReceivable()
    {
        return $this->financialReportsRepository->getAccountsReceivable();
    }

    public function netProfit($from , $to)
    {
        $profitOffer= $this->financialReportsRepository->getNetProfitPerOffers($from ,$to);
        $profitItem =$this->financialReportsRepository->getNetProfitPerItem($from ,$to);

        $totalRevenue = $profitOffer->sum('total_revenue') + $profitItem->sum('total_revenue');
        $totalCost = $profitOffer->sum('total_cost') + $profitItem->sum('total_cost');
        $netProfit = $profitOffer->sum('net_profit') + $profitItem->sum('net_profit');

        return [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'net_profit' => $netProfit
        ];

    }

    public function getNetProfitPerOffers()
    {
        return $this->financialReportsRepository->getNetProfitPerOffers();
    }


}
