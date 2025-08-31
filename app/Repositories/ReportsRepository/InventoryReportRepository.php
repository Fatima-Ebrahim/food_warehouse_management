<?php
namespace App\Repositories\ReportsRepository;


use App\Models\Item;
use App\Models\ItemTotalQuantity;
use App\Models\OrderBatchDetail;
use App\Models\OrderItem;
use App\Models\OrderOfferItemBatchDetails;
use App\Models\PurchaseReceiptItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryReportRepository
{


    public function getCurrentStock()
    {
        return ItemTotalQuantity::query()->get(['item_id', "item_name", 'total_quantity_in_base_unit']);

    }


    public function getLowStockItems()
    {
        return ItemTotalQuantity::query()->whereRaw('total_quantity_in_base_unit <= minimum_stock_level')->get();
    }

    public function getMovements(string $from, string $to, string $type = 'all', int $perPage = 100, ?int $itemId = null)
    {
        $q = StockMovement::query()
            ->betweenDates($from, $to)
            ->ofType($type === 'all' ? null : $type)
            ->forItem($itemId)
            ->orderBy('movement_date', 'desc');

        return $q->paginate($perPage);
    }

//    public function getNetMovementForItem(int $itemId, string $from, string $to): float
//    {
//        $row = StockMovement::query()
//            ->forItem($itemId)
//            ->betweenDates($from, $to)
//            ->selectRaw('COALESCE(SUM(quantity_in_base_unit), 0) as net_qty')
//            ->first();
//
//        return (float)($row->net_qty ?? 0);
//    }


    public function getBatchesStatus(string $status = 'all', string $asOf = null, int $perPage = 50)
    {
        $asOf = $asOf ? Carbon::parse($asOf)->startOfDay() : Carbon::now()->startOfDay();

        $query = PurchaseReceiptItem::query()
            ->select([
                'purchase_receipt_items.id',
                'purchase_receipt_items.purchase_order_id',
                'purchase_receipt_items.item_id',
                'purchase_receipt_items.unit_id',
                'purchase_receipt_items.quantity as original_quantity',
                'purchase_receipt_items.quantity_in_base_unit',
                'purchase_receipt_items.available_quantity',
                'purchase_receipt_items.price',
                'purchase_receipt_items.total_price',
                'purchase_receipt_items.production_date',
                'purchase_receipt_items.expiry_date',
                'purchase_receipt_items.created_at',
                'purchase_receipt_items.updated_at',
                DB::raw('(purchase_receipt_items.quantity - purchase_receipt_items.available_quantity) as consumed_quantity'),
                DB::raw('CASE WHEN purchase_receipt_items.quantity > 0 THEN ROUND(( (purchase_receipt_items.quantity - purchase_receipt_items.available_quantity) / purchase_receipt_items.quantity) * 100, 2) ELSE 0 END as consumed_percent')
            ])
            ->with('item') // eager load product
            ->orderBy('created_at', 'desc');

        // فلترة حسب الحالة
        if ($status === 'consumed') {
            $query->whereColumn('available_quantity', '<=', 0);
        } elseif ($status === 'remaining') {
            $query->where('available_quantity', '>', 0);
        } elseif ($status === 'expired') {
            $query->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', $asOf->toDateString());
        }

        // إضافة حقل days_to_expiry و expired boolean ضمن النتائج بعد الارسال
        $paginator = $query->paginate($perPage);

        $paginator->getCollection()->transform(function ($batch) use ($asOf) {
            $expiryDate = $batch->expiry_date ? Carbon::parse($batch->expiry_date) : null;
            $daysToExpiry = $expiryDate ? $expiryDate->diffInDays($asOf, false) : null; // negative => expired
            return [
                'batch_id' => $batch->id,
                'purchase_order_id' => $batch->purchase_order_id,
                'item_id' => $batch->item_id,
                'item_name' => $batch->item->name ?? null,
                'unit_id' => $batch->unit_id,
                'original_quantity' => (float)$batch->original_quantity,
                'available_quantity' => (float)$batch->available_quantity,
                'consumed_quantity' => (float)$batch->consumed_quantity,
                'consumed_percent' => (float)$batch->consumed_percent,
                'unit_price' => $batch->price !== null ? (float)$batch->price : null,
                'total_price' => $batch->total_price !== null ? (float)$batch->total_price : null,
                'production_date' => $batch->production_date ? $batch->production_date->toDateString() : null,
                'expiry_date' => $batch->expiry_date ? $batch->expiry_date->toDateString() : null,
                'is_expired' => $expiryDate ? $expiryDate->lt($asOf) : false,
                'days_to_expiry' => $daysToExpiry,
                'created_at' => $batch->created_at ? $batch->created_at->toDateTimeString() : null,
            ];
        });

        return $paginator;
    }

    private function applyItemFilters($query, array $filters)
    {
        // الفلترة الزمنية
        if (!empty($filters['months'])) {
            $from = Carbon::now()->subMonths($filters['months'])->startOfDay();
            $query->where('orders.updated_at', '>=', $from);
        }

        if (!empty($filters['from'])) {
            $query->where('orders.updated_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->where('orders.updated_at', '<=', $filters['to']);
        }


        return $query;
    }

    public function getTopMovingItems(array $filters = [])
    {
        $query = DB::table('items')
            ->select(
                'items.id',
                'items.name',
                DB::raw('
                    COALESCE(SUM(order_items.quantity), 0)
                    + COALESCE(SUM(special_offer_items.required_quantity * order_offers.quantity), 0) as total_quantity
                ')
            )
            ->leftJoin('item_units','item_units.item_id','=','items.id')
            ->leftJoin('order_items', 'order_items.item_unit_id', '=', 'item_units.id')
            ->leftJoin('orders', function ($join) {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->whereIn('orders.status', ['paid','partially_paid']);
            })
            ->leftJoin('order_offers', 'order_offers.order_id', '=', 'orders.id')
            ->leftJoin('special_offer_items', 'special_offer_items.offer_id', '=', 'order_offers.offer_id')
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('total_quantity');

        $query = $this->applyItemFilters($query, $filters);

        if (!empty($filters['limit'])) {
            $query->limit($filters['limit']);
        }
        else{
            $query->limit(10);
        }

        return $query->get();
    }



    public function getSlowMovingItems(array $filters = [])
    {
        $query = DB::table('items')
            ->select(
                'items.id',
                'items.name',
                DB::raw('
                    COALESCE(SUM(order_items.quantity), 0)
                    + COALESCE(SUM(special_offer_items.required_quantity * order_offers.quantity), 0) as total_quantity
                ')
            )
            ->leftJoin('item_units','item_units.item_id','=','items.id')
            ->leftJoin('order_items', 'order_items.item_unit_id', '=', 'item_units.id')
            ->leftJoin('orders', function ($join) {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->whereIn('orders.status', ['paid','partially_paid']);
            })

            ->leftJoin('order_offers', 'order_offers.order_id', '=', 'orders.id')
            ->leftJoin('special_offer_items', 'special_offer_items.offer_id', '=', 'order_offers.offer_id')
            ->groupBy('items.id', 'items.name')
            ->orderBy('total_quantity', 'asc');

        $query = $this->applyItemFilters($query, $filters);

        if (!empty($filters['limit'])) {
            $query->limit($filters['limit']);
        }
        else{
            $query->limit(10);
        }

        return $query->get();
    }
}
