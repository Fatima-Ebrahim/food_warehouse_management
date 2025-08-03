<?php
namespace App\Repositories\Costumer;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointsRepository{

    public function get($userId){
        return User::find($userId)->customer->points;
    }

    public function update($userId , $numberOfPoints){
        $customerId= User::find($userId)->customer->id;
         Customer::query()->where('id',$customerId)->update(['points' => $numberOfPoints]);
         return true;
      //  return Customer::find($userId)->points;
    }




}
