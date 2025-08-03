<?php

namespace App\Services\Orders;

use App\Repositories\Costumer\OrderRepository;
use App\Repositories\Costumer\PointsRepository;
use App\Repositories\CustomerRepository;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseReceiptItem;

class PointsService
{
    protected $customerRepository;
    public function __construct(CustomerRepository $repository)
    {
        $this->customerRepository=$repository;
    }

    public function getPoints($userId)
    {

        return $this->customerRepository->getPoints($userId);
    }
    public function addPoints($numberOfPoints, $userId)
    {
        $this->customerRepository->addPoints($userId ,$numberOfPoints);
    }

    public function subPoints($numberOfPoints, $userId){
      $this->customerRepository->subtractPoints($userId ,$numberOfPoints);
    }


}
