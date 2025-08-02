<?php
namespace App\Repositories\Orders;

use App\Models\Installment;
use App\Models\User;

class InstallmentRepository{

    public function create($data){
        return Installment::create($data);
    }

//    public function getAllUserInstallment($userId){
//        return
//    }

}
