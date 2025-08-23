<?php
namespace App\Services\ReportsService;


use App\Models\SalesByCustomer;
use App\Repositories\ReportsRepository\SalesReportRepository;
use Illuminate\Support\Facades\DB;

class SalesReportService{
    public function __construct(protected SalesReportRepository $salesReportRepository)
    {
    }


    public function aggregateSales(array $filters)
    {
        return $this->salesReportRepository->aggregateSales($filters);
    }

    public function salesByPaymentType(array $data )
    {
        return $this->salesReportRepository->salesByPaymentType($data);
    }

    public function ordersDeliveryStatus(array $data )
    {
        return $this->salesReportRepository->ordersDeliveryStatus($data,$data['status']);
    }

    public function customerStatement($userId)
    {
        return $this->salesReportRepository->getCustomerStatementByUserId($userId);
    }


    public function getSalesByCustomer($from , $to)
    {
        return $this->salesReportRepository->getSalesByCustomer($from ,$to );
    }

    public function getSalesByProductReport($from ,$to ,$sort)
    {
        return $this->salesReportRepository->getSalesByProduct($from ,$to,$sort);
    }


    public function topCustomers(array $data)
    {
            return $this->salesReportRepository->
            getTopCustomers($data['getBy'], $data['paymentType'] , $data['from'] , $data['to'] );


    }

}
