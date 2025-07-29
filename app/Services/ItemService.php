<?php
namespace App\Services;
use App\Http\Resources\Items\ItemResource;
use App\Models\Item;
use App\Repositories\ItemRepository;
use App\Repositories\ItemUnitRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItemService{

    protected $ItemRepository;
    protected $itemUniteRepository ;
    public function __construct(ItemRepository $item, ItemUnitRepository $itemUnitRepository)
    {
        $this->ItemRepository=$item;
        $this->itemUniteRepository=$itemUnitRepository;
    }

    public function createItem(array $data): Item
    {
        $image = $data['image'] ?? null;
        unset($data['image']);

        return DB::transaction(function () use ($data, $image) {
            $item = $this->ItemRepository->create($data);
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
        return $this->ItemRepository->update($item, $data);
    }

    public function getItems($category_id)
    {
        $items=collect($this->ItemRepository->getItemsInCategory($category_id));

        return $items->map(function ($item) {

            return [
                'id' => $item->id,
                'name'=>$item->name,
                'image' => $item->image,
                'type'=>'item',
                'next'=>'item',
                'has_children'=>false,
            ];
        });
    }

    public function  getAllItems(){
        $items=collect($this->ItemRepository->getAllItems());
        return $items->map(function ($item) {

            return [
                'id' => $item->id,
                'name'=>$item->name,
                'image' => $item->image,
            ];
        });
    }

    public function getById($id){
        $item= $this->ItemRepository->getById($id);
        if ($item->image) {
            $item->image_url = Storage::url($item->image);
        }

        return new ItemResource($item);
    }

    public function getImagePath($id)
    {
        $request = $this->ItemRepository->getById($id);
        return $request->image;
    }

}
