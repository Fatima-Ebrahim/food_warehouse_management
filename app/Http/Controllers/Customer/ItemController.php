<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Item;
use App\Models\Order;
use App\Services\ItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function __construct(protected ItemService $itemService)
    {
    }


    public function index($category_id)
    {
      $items=  $this->itemService->getItems($category_id);
        return response()->json($items);
    }


    public function store(StoreItemRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        try {
            $item = $this->itemService->createItem($data);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item: ' . $e->getMessage(),
            ], 500);
        }
        return response()->json([
            'success' => true,
            'data' => $item,
            'message' => 'Item created successfully',
        ], 201);
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        $item = $this->itemService->update($item, $request->validated());
        return response()->json(['message' => 'Item updated successfully', 'data' => $item]);
    }
//    public function baseUnitForItem(){
//
//    }
    public function getAllItems(){
        $items= $this->itemService->getAllItems();
        return response()->json($items);
    }
    public function getItemById($id){
        $data=$this->itemService->getById($id);
        return response()->json($data);
    }

    public function showItemImage($id)
    {
        $path = $this->itemService->getImagePath($id);

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->file(storage_path("app/public/{$path}"));
    }


    public function getAllReceiptItemForItem($itemId){
        $receipts=$this->itemService->getAllReceiptItemForItem($itemId);
        return response()->json(['data'=>$receipts],200);
    }

    public function getItemFIFORecommendation(Order $order){
        $receipts=$this->itemService->getItemFIFORecommendation($order);
        return response()->json(['data'=>$receipts],200);
    }

    public function deleteItem( $itemId){
        $bool=$this->itemService->deleteItem($itemId);
        if($bool)
        return response()->json(['status '=>true ,
                                'message'=>'item deleted successfully'] ,200);
        else{
            return response()->json(['status '=>false ,
                'message'=>'item not found'] ,200);
        }
    }

}
