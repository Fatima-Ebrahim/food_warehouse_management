<?php
namespace App\Services;

use App\Repositories\ItemRepository;
use App\Repositories\ItemUnitRepository;

class ItemUnitService
{

    protected $itemRepository;
    protected $itemUnitRepository;

    public function __construct(ItemRepository $itemRepository, ItemUnitRepository $itemUnitRepository)
    {
        $this->itemRepository = $itemRepository;
        $this->itemUnitRepository = $itemUnitRepository;
    }

    public function store(array $data)
    {
        $baseUnitId = $this->itemRepository->getBaseUnitId($data['item_id']);


        if ($data['unit_id'] == $baseUnitId || !empty($data['selling_price'])) {
            $itemUnit = $this->itemUnitRepository->create($data);
        }
        else {
            $baseUnitPrice = $this->itemUnitRepository->getPrice($data['item_id'], $baseUnitId);
            $data['selling_price'] = $baseUnitPrice * $data['conversion_factor'];
            $itemUnit = $this->itemUnitRepository->create($data);
        }

        return [
            'data' => $itemUnit,
            'message' => 'Created successfully'
        ];
    }

    public function getItemUnit(int $itemId, int $unitId): array
    {

        $itemUnit = $this->itemUnitRepository->findByItemAndUnit($itemId, $unitId);

        return $itemUnit ?
            ['success' => true, 'data' => $itemUnit] :
            ['success' => false, 'message' => 'Item unit not found'];

    }

    public function getAllItemUnits($itemId): array
    {

        $itemUnit = $this->itemUnitRepository->getAllItemUnitsById($itemId);

        return $itemUnit ?
            ['success' => true, 'data' => $itemUnit] :
            ['success' => false, 'message' => 'Item unit not found'];

    }
}
