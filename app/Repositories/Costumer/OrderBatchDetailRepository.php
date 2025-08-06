<?php
namespace App\Repositories\Costumer;

use App\Models\Installment;
use App\Models\OrderBatchDetail;
use App\Models\User;

class OrderBatchDetailRepository{

    public function create($data){
        return OrderBatchDetail::create($data);
    }



}
