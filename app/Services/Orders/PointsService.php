<?php

namespace App\Services\Orders;

use App\Repositories\Orders\OrderRepository;
use App\Repositories\Orders\PointsRepository;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseReceiptItem;

class PointsService
{
    protected $pointsRepository;
    public function __construct(PointsRepository $repository)
    {
        $this->pointsRepository=$repository;
    }

    public function getPoints()
    {
        $id=auth()->user()->id;
        return $this->pointsRepository->get($id);
    }
    public function addPoints($numberOfPoints)
    {
        $id=auth()->user()->id;
        $currentNumber=$this->pointsRepository->get($id);
        $finalNumber= $currentNumber +$numberOfPoints;
        return $this->pointsRepository->update($id,$finalNumber);
    }

}
