<?php
namespace App\Services;
use App\Http\Resources\Items\ItemResource;
use App\Models\Item;
use App\Repositories\CategoryRepository;
use App\Repositories\Costumer\OrderRepository;
use App\Repositories\Costumer\PurchaseReceiptitemRepository;
use App\Repositories\itemRepository;
use App\Repositories\ItemUnitRepository;
use App\Services\Orders\FifoStockDeductionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItemService{


    public function __construct(
                                protected itemRepository $itemRepository,
                                protected ItemUnitRepository $itemUniteRepository ,
                                protected CategoryRepository $categoryRepository ,
                                protected PurchaseReceiptitemRepository $receiptitemRepository ,
                                protected OrderRepository $orderRepository)
    {
    }

    public function createItem(array $data): Item
    {
        $image = $data['image'] ?? null;
        unset($data['image']);

        return DB::transaction(function () use ($data, $image) {
            $item = $this->itemRepository->create($data);
            if ($image) {
                $imagePath = $image->store('items', 'public');
                $item->update(['image' => $imagePath]);
            }

            $this->createDefaultSellingUnit($item, $data['base_unit_id'], $data['selling_price']);
            return $item;
        });
    }


    private function createDefaultSellingUnit(Item $item, int $unitId, float $sellingPrice): void
    {
       $this->itemUniteRepository->create([
            'item_id' => $item->id,
            'unit_id' => $unitId,
            'is_default' => 0,
            'selling_price' => $sellingPrice,
            'conversion_factor' => 1,
        ]);

    }

    public function update(Item $item, array $data)
    {
        return $this->itemRepository->update($item, $data);
    }

    public function getItems($category_id)
    {
        $lastLevelCats=app(CategoryService::class)->getLastLevelForCat($category_id);
        $data=collect();
        foreach ($lastLevelCats as $lastLevelCat){
            $items=collect($this->itemRepository->getItemsInCategory($lastLevelCat));
       $data=$data->merge($items->map(function ($item) {
            return [
                'id' => $item->id,
                'name'=>$item->name,
                'image' => $item->image,
            ];
        }));}
        return $data;
    }

    public function  getAllItems(){
        $items=collect($this->itemRepository->getAllItems());
        return $items->map(function ($item) {

            return [
                'id' => $item->id,
                'name'=>$item->name,
                'image' => $item->image,
            ];
        });
    }

    public function getById($id)
    {
        $item= $this->itemRepository->getById($id);
        if ($item->image) {
            $item->image_url = Storage::url($item->image);
        }

        return new ItemResource($item);
    }

    public function getImagePath($id)
    {
        $request = $this->itemRepository->getById($id);
        return $request->image;
    }


    public function getAllReceiptItemForItem($itemId){
        return $this->receiptitemRepository->getAllReceiptItemForItem($itemId);
    }
    public function getItemFIFORecommendation($order){
        $order =$this->orderRepository->getOrderWithRelations($order);
        return app(FifoStockDeductionService::class)->FIFORecommendation($order);
    }
    public function deleteItem($itemId){
        $item=$this->itemRepository->getById($itemId);
        if($item)
        return $this->itemRepository->deleteItem($item);
        else return false;

    }


}
