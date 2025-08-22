<?php
namespace App\Repositories\ReportsRepository;

use App\Models\CustomerStatement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialReportsRepository{


    public function getAccountsReceivable(){
        return CustomerStatement::query()->select(
            ['customer_id', 'customer_name'  ,
                DB::raw('SUM(remaining_balance) as remaining_balance'),
                DB::raw('SUM(total_installments_paid) as total_installments_paid')
            ])->whereNot('payment_type','paid')
            ->groupBy('customer_id','customer_name')
            ->orderBy('remaining_balance', 'desc')->get();
    }

    public function getNetProfitPerItem($startDate=null, $endDate=null)
    {
        $query= DB::table('order_batch_details as obd')
            ->join('order_items as oi','obd.order_item_id','=','oi.id')
            ->join('item_units as iu','oi.item_unit_id','=','iu.id')
            ->join('items as i','iu.item_id','=','i.id')

            ->join('purchase_receipt_items as pri','obd.purchase_receipt_item_id' ,'=','pri.id')
            ->join('item_units as iu_buy', function ($j) {
                $j->on('iu_buy.item_id', '=', 'pri.item_id')
                    ->on('iu_buy.unit_id', '=', 'pri.unit_id');
            })
            ->select([
                'i.id as item_id',
                'i.name as item_name',
                DB::raw("SUM(oi.price) as total_revenue"),
                DB::raw("SUM((pri.price / NULLIF(iu_buy.conversion_factor, 0)) * obd.quantity) as total_cost"),
                DB::raw("SUM( oi.price - ((pri.price / iu_buy.conversion_factor)* obd.quantity)) as net_profit ")
            ])

            ->groupBy('i.id', 'i.name');
        if ($startDate && $endDate) {
            $query ->whereBetween('pri.created_at', [$startDate, $endDate]);
        }
        return  $query->orderBy('net_profit','desc')->get();

    }

    public function getNetProfitPerOffers($startDate = null, $endDate = null)
    {
        $query = DB::table('order_offers as oo')
            ->join('special_offers as so', 'oo.offer_id', '=', 'so.id')
            ->join('special_offer_items as soi', 'so.id', '=', 'soi.offer_id')
            ->join('order_offer_item_batch_details as ooibd', function($join) {
                $join->on('ooibd.order_offer_id', '=', 'oo.id')
                    ->on('ooibd.order_offer_items_id', '=', 'soi.id');
            })
            ->join('purchase_receipt_items as pri', 'ooibd.purchase_receipt_item_id', '=', 'pri.id')
            ->join('item_units as iu_buy', function ($j) {
                $j->on('iu_buy.item_id', '=', 'pri.item_id')
                    ->on('iu_buy.unit_id', '=', 'pri.unit_id');
            })
            ->select([
                'so.id as offer_id',
                'so.description as offer_description',
                DB::raw("SUM(oo.price) as total_revenue"),
                DB::raw("SUM((pri.price / NULLIF(iu_buy.conversion_factor, 0)) * ooibd.quantity) as total_cost"),
                DB::raw("SUM(oo.price - ((pri.price / iu_buy.conversion_factor) * ooibd.quantity)) as net_profit")
            ])
            ->groupBy('so.id', 'so.description');

        if ($startDate && $endDate) {
            $query->whereBetween('ooibd.created_at', [$startDate, $endDate]);
        }

        return $query->orderBy('net_profit','desc')->get();
    }

}
