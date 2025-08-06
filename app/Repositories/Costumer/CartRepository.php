<?php
namespace App\Repositories\Costumer;
use App\Models\Cart;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\User;

class CartRepository{

    public function getUserCart($userId){
        return User::find($userId)->cart;
    }

}
