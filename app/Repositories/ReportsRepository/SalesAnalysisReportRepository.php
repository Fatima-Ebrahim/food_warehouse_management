<?php
namespace App\Repositories\ReportsRepository;


use App\Models\Order;
use Illuminate\Support\Facades\DB;

class SalesAnalysisReportRepository{


    public function getSalesTotalByPeriod($period)
    {
        return Order::whereMonth('created_at', date('m', strtotime($period)))
            ->whereYear('created_at', date('Y', strtotime($period)))
            ->sum('total_price');
    }


    public function getInventoryTurnover($startDate, $endDate)
    {
        // كل المنتجات
        $items = DB::table('items')->select('id', 'name')->get();

        $results = [];

        foreach ($items as $item) {
            // 1) إجمالي الكميات المباعة (outgoing) خلال الفترة
            $totalSales = DB::table('stock_movements_view')
                ->where('item_id', $item->id)
                ->where('type', 'outgoing')
                ->whereBetween('movement_date', [$startDate, $endDate])
                ->sum('quantity');

            // 2) المخزون أول الفترة (كل الوارد - كل الصادر قبل startDate)
            $incomingBefore = DB::table('stock_movements_view')
                ->where('item_id', $item->id)
                ->where('type', 'incoming')
                ->where('movement_date', '<', $startDate)
                ->sum('quantity');

            $outgoingBefore = DB::table('stock_movements_view')
                ->where('item_id', $item->id)
                ->where('type', 'outgoing')
                ->where('movement_date', '<', $startDate)
                ->sum('quantity');

            $openingStock = $incomingBefore - $outgoingBefore;

            // 3) المخزون آخر الفترة (كل الوارد - كل الصادر لحد endDate)
            $incomingUntil = DB::table('stock_movements_view')
                ->where('item_id', $item->id)
                ->where('type', 'incoming')
                ->where('movement_date', '<=', $endDate)
                ->sum('quantity');

            $outgoingUntil = DB::table('stock_movements_view')
                ->where('item_id', $item->id)
                ->where('type', 'outgoing')
                ->where('movement_date', '<=', $endDate)
                ->sum('quantity');

            $closingStock = $incomingUntil - $outgoingUntil;

            // 4) متوسط المخزون
            $averageInventory = ($openingStock + $closingStock) / 2;

            // 5) نسبة دوران المخزون
            $inventoryTurnover = $averageInventory > 0
                ? $totalSales / $averageInventory
                : 0;

            $results[] = [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'total_sales' => $totalSales,
                    'opening_stock' => $openingStock,
                'closing_stock' => $closingStock,
                'average_inventory' => $averageInventory,
                'inventory_turnover' => round($inventoryTurnover, 2),
            ];
        }

        return collect($results)->sortByDesc('inventory_turnover')->values();
    }


    public function getOffersImpactWithIncrease($daysBefore , $daysAfter )
    {
        $offers = DB::table('special_offers')->get();
        $results = [];

        foreach($offers as $offer){
            // فترة قبل العرض
            $beforeStart = date('Y-m-d', strtotime($offer->starts_at . " -{$daysBefore} days"));
            $beforeEnd = date('Y-m-d', strtotime($offer->starts_at . " -1 day"));

            // فترة بعد العرض
            $afterStart = date('Y-m-d', strtotime($offer->ends_at . " +1 day"));
            $afterEnd = date('Y-m-d', strtotime($offer->ends_at . " +{$daysAfter} days"));

            // 1) المبيعات قبل العرض
            $salesBefore = DB::table('stock_movements_view')
                ->where('type','outgoing')
                ->where('movement_date','>=',$beforeStart)
                ->where('movement_date','<=',$beforeEnd)
                ->sum('quantity');

            // 2) المبيعات أثناء العرض
            $salesDuring = DB::table('stock_movements_view')
                ->where('type','outgoing')
                ->where('movement_date','>=',$offer->starts_at)
                ->where('movement_date','<=',$offer->ends_at)
                ->sum('quantity');

            // 3) المبيعات بعد العرض
            $salesAfter = DB::table('stock_movements_view')
                ->where('type','outgoing')
                ->where('movement_date','>=',$afterStart)
                ->where('movement_date','<=',$afterEnd)
                ->sum('quantity');

            // 4) نسبة زيادة المبيعات أثناء العرض مقارنة بفترة قبل العرض
            $increasePercent = $salesBefore > 0
                ? (($salesDuring - $salesBefore) / $salesBefore) * 100
                : ($salesDuring > 0 ? 100 : 0); // إذا ما في مبيعات قبل العرض، نعتبر أي مبيعات زيادة 100%

            $results[] = [
                'offer_id' => $offer->id,
                'description' => $offer->description,
                'starts_at' => $offer->starts_at,
                'ends_at' => $offer->ends_at,
                'sales_before' => $salesBefore,
                'sales_during' => $salesDuring,
                'sales_after' => $salesAfter,
                'increase_percent' => round($increasePercent, 2), // تقريب لمنزلتين عشريتين
            ];
        }

        // ترتيب حسب أفضل تأثير (أكبر مبيعات أثناء العرض)
        return collect($results)->sortByDesc('sales_during')->values();
    }

}
