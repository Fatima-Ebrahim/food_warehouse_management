<?php

namespace App\Http\Controllers\Orders;
use App\Http\Controllers\Controller;
use App\Repositories\ItemUnitRepository;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    protected ItemUnitRepository $itemUnitRepository;
    public function __construct(ItemUnitRepository $itemUnitRepository)
    {
        $this->itemUnitRepository = $itemUnitRepository;
    }



}
