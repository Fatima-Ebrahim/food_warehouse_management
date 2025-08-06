<?php
namespace App\Services\Orders;

use App\Models\Item;
use App\Models\ItemUnit;
use App\Repositories\Costumer\CartRepository;

class CartService{

    protected $cartRepository;

    public function __construct(CartRepository $cartRepo)
    {
        $this->cartRepository = $cartRepo;
    }



}
