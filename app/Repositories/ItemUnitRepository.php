<?php

namespace App\Repositories;
use App\Models\Item;
use App\Models\ItemUnit;
class ItemUnitRepository{

    protected ItemRepository $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository=$itemRepository;
    }

    public function create(array $data): ItemUnit
    {
        return ItemUnit::query()->create($data);
    }


    public function getPrice(int $itemId, int $unitId)
    {
        return ItemUnit::where('item_id', $itemId)
            ->where('unit_id', $unitId)
            ->value('selling_price');
    }

    public function findByItemAndUnit(int $itemId, int $unitId): ?ItemUnit
    {
        return ItemUnit::query()
            ->where('item_id', $itemId)
            ->where('unit_id', $unitId)
            ->first();
    }

    public function getAllItemUnitBuItemId($item_id){
        return ItemUnit::query()->where('item_id',$item_id)->get();
    }

}
