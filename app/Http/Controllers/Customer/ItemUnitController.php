<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexItemUnitRequest;
use App\Http\Requests\StoreItemUnitRequest;
use App\Services\ItemUnitService;
use Illuminate\Http\Request;

class ItemUnitController extends Controller
{
protected ItemUnitService $itemUnitService;

    public function __construct(ItemUnitService $service)
    {
        $this->itemUnitService=$service;
    }

    public function show(IndexItemUnitRequest $request)
    {
        $validated = $request->validated();
        $result = $this->itemUnitService->getItemUnit(
            $validated['item_id'],
            $validated['unit_id']
        );

        return response()->json($result, $result['success'] ? 200 : 404);
    }

    public function store(StoreItemUnitRequest $request){
        $data=$request->validated();
        $unit= $this->itemUnitService->store($data);
        return response()->json($unit);
    }
    public function showAll($item_id)
    {
        $result = $this->itemUnitService->getAllItemUnits($item_id);

        return response()->json($result, $result['success'] ? 200 : 404);
    }
    public function update(){}
    public function delete(){}

}
