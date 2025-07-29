<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    protected $ItemService;
    public function __construct(ItemService $item)
    {
        $this->ItemService=$item;
    }


    public function index($category_id)
    {
      $items=  $this->ItemService->getItems($category_id);
        return response()->json($items);
    }

    public function store(StoreItemRequest $request)
    {

        $data=$request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }
        $result = $this->ItemService->createItem($data);
        return response()->json($result, 200);
    }


    public function update(UpdateItemRequest $request, Item $item)
    {
        $item = $this->ItemService->update($item, $request->validated());
        return response()->json(['message' => 'Item updated successfully', 'data' => $item]);
    }
    public function baseUnitForItem(){

    }
    public function getAllItems(){
        $items= $this->ItemService->getAllItems();
        return response()->json($items);
    }
    public function getItemById($id){
        $data=$this->ItemService->getById($id);
        return response()->json($data);
    }

}
