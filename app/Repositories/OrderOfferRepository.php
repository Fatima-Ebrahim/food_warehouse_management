<?php
namespace App\Repositories;



use App\Models\CartOffer;
use App\Models\OrderOffer;

class OrderOfferRepository{

    public function create(array $data){
        return OrderOffer::query()->create($data);
    }

    public function getOfferByOrderId($orderId){
        return OrderOffer::query()->where('order_id',$orderId)->
        with('offer.Items')->get();
    }




}
