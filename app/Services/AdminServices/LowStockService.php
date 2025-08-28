<?php

namespace App\Services\AdminServices;

use App\Repositories\AdminRepository\LowStockRepository;
use App\Notifications\ItemLowStockNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class LowStockService
{
    protected $lowStockRepository;

    public function __construct(LowStockRepository $lowStockRepository)
    {
        $this->lowStockRepository = $lowStockRepository;
    }

    public function processLowStockItemsAndNotify()
    {
        $lowStockItems = $this->lowStockRepository->getLowStockItems();

        if ($lowStockItems->isEmpty()) {
            return;
        }


        $itemsData = $lowStockItems->map(function ($item) {
            return [
                'item_id' => $item->item_id,
                'item_name' => $item->item_name,
                'total_quantity_in_base_unit' => $item->total_quantity_in_base_unit,
                'minimum_stock_level' => $item->minimum_stock_level,
            ];
        })->toArray();

        $manager = User::query()->where('user_type','=','admin')->get();

        if ($manager) {

            Notification::send($manager, new ItemLowStockNotification($itemsData));
        }
    }
}
