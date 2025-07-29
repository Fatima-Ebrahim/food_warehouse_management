<?php
namespace App\Repositories;

use App\Models\Category;
use App\Models\Item;
use App\Models\Unit;

class ItemRepository{

    public function getItemsInCategory($category_id){
        return Category::query()->find($category_id)->items;
    }

    public function create(array $data)
    {
        return Item::query()->create($data);
    }

    public function itemBaseUnit($base_unit_id){
        return Unit::query()->find($base_unit_id)->name;

    }
    public function update(Item $item, array $data): Item
    {
        $item->update($data);
        return $item;
    }

    public function getAllItems(){
        return Item::where('deleted_at',null)->get();
    }
    public function getById(int $id)
    {
        return Item::query()->find($id);
    }
    public function getBaseUnitId($itemId){
        return Item::find($itemId)->base_unit_id;
    }
}
