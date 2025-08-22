<?php
namespace App\Repositories\ReportsRepository;

    use App\Models\CustomerStatement;
    use App\Models\Order;
    use App\Models\SalesByCustomer;
    use App\Models\SalesByProduct;
    use Carbon\Carbon;
    use Illuminate\Queue\RedisQueue;
    use Illuminate\Support\Facades\DB;
    use App\Models\User;

    class SalesReportRepository
{
    private function applyDateFilters($query, array $filters)
    {
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

//used
    public function aggregateSales(array $filters)
    {
        $group = $filters['group_by'] ?? 'day';
        $format = match($group) {
            'year'  => '%Y',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $query = DB::table('orders')
            ->whereIn('orders.status',['paid','partially_paid'])
            ->select(
                DB::raw("DATE_FORMAT(orders.updated_at, '{$format}') as period"),
                DB::raw('SUM(orders.final_price) as total_sales'),
                DB::raw('COUNT(orders.id) as orders_count')
            )
            ->groupBy('period')
            ->orderBy('period','asc');

        $this->applyDateFilters($query,$filters);

        return $query->get();
    }

   //used
    public function salesByPaymentType(array $data)
    {
        $query = DB::table('orders')
            ->whereIn('orders.status',['paid','partially_paid'])
            ->select('payment_type', DB::raw('SUM(final_price) as total_sales'),
                DB::raw('COUNT(id) as orders_count'))
            ->groupBy('payment_type');
        if($data['paymentType']){
            $query->where('payment_type',$data['paymentType']);
        }
        $this->applyDateFilters($query,$data);

        return $query->get();
    }

//used
    public function ordersDeliveryStatus(array $data ,$status )
    {
        $query = DB::table('orders as o')
            ->join('carts as cart', 'o.cart_id', '=', 'cart.id')
            ->join('users as u', 'cart.user_id', '=', 'u.id')
            ->select(
                'o.id as order_id',
                'u.id as customer_id',
                'u.name as customer_name',
                'o.final_price as invoice_amount',
                'o.status',
                'o.payment_type',
                'o.updated_at as date',
                DB::raw('CASE
                    WHEN o.status IN ("paid", "partially_paid") THEN "received"
                    WHEN o.status = "confirmed" THEN "not_received"
                    ELSE "other"
                END as order_type')
            )
            ->whereIn('o.status', ['paid', 'partially_paid', 'confirmed']);


        if ($status === 'received') {
            $query->whereIn('o.status', ['paid', 'partially_paid']);
        } elseif ($status === 'not_received') {
            $query->where('o.status', 'confirmed');
        }
        else {
            $query->whereIn('o.status',['confirmed','paid','partially_paid']);
        }
        if($data['from']&&$data['to']){
            $query->whereBetween('o.updated_at',[$data['from'],$data['to']]);
        }
        $orders = $query->orderBy('o.updated_at', 'desc')
            ->get();

        return [
            'filter_type' => $status,
            'data' => $orders
        ];
    }

    //used
    public function getCustomerStatementByUserId(int $userId)
    {
        return CustomerStatement::where('customer_id', $userId)->get();
    }
//used
    public function getSalesByCustomer($from, $to)
    {


        if ($from && $to) {
            $query=SalesByCustomer::whereBetween('date', [$from, $to])->get();
        }
        else{
            $query = SalesByCustomer::all();
        }

        return $query;
    }
//done
    public function getSalesByProduct($from , $to , $sort )
    {
        $query = SalesByProduct::query()
            ->select([
                'item_id',
                'item_name',
                DB::raw('SUM(total_quantity) as total_quantity'),
                DB::raw('SUM(total_value) as total_value')
            ])
            ->groupBy('item_id', 'item_name')
            ->orderBy('total_quantity', $sort);

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return $query->get();
    }

    public function allCustomerStatement()
    {
        return CustomerStatement::all();
    }


//used

        public function getTopCustomers(string $sortBy,string $paymentType,string $from,string $to) {
            $query = User::where('user_type', 'customer')
                ->select([
                    'users.id',
                    'users.name',
                    'users.email',
                    DB::raw('SUM(orders.final_price) as total_sales'),
                    DB::raw('COUNT(orders.id) as orders_count')
                ])
                ->join('carts', 'carts.user_id', '=', 'users.id')
                ->join('orders', 'orders.cart_id', '=', 'carts.id')
                ->whereIn('orders.status', ['paid', 'partially_paid'])
                ->groupBy('users.id', 'users.name', 'users.email');


            if ($paymentType)
            {
                $query->where('orders.payment_type', $paymentType);
            }

            if ($from && $to)
            {
                $query->whereBetween('orders.updated_at', [$from, $to]);
            }


            if ($sortBy === 'OrdersNumber')
            {
                $query->orderByDesc('orders_count');
            }
            else
            {
                $query->orderByDesc('total_sales');
            }

            return $query->get();
        }

    }
