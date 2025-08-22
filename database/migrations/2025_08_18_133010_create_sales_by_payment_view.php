<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
    CREATE OR REPLACE VIEW sales_by_payment_view AS
    SELECT
        o.payment_type,
        COUNT(o.id) AS orders_count,
        SUM(o.final_price) AS total_sales,
        COALESCE(SUM(i.paid_amount), 0) AS total_installments_paid,
        CASE
            WHEN o.payment_type = 'cash' THEN 0
            ELSE SUM(o.final_price) - COALESCE(SUM(i.paid_amount), 0)
        END AS remaining_balance
    FROM orders o
    LEFT JOIN installments i ON i.order_id = o.id
    WHERE o.status IN ('paid', 'partially_paid')
    GROUP BY o.payment_type
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_by_payment_view');
    }
};
