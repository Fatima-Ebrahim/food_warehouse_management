<?php


namespace App\Repositories\AdminRepository;

use App\Models\ItemTotalQuantity;
use Illuminate\Database\Eloquent\Collection;

class LowStockRepository
{
    public function getLowStockItems()
    {
        return ItemTotalQuantity::query()->whereRaw('total_quantity_in_base_unit <= minimum_stock_level')->get();
    }
}
