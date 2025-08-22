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
    CREATE OR REPLACE VIEW customer_statement_view AS
        SELECT
            u.id AS customer_id,
            u.name AS customer_name,
            o.id AS order_id,
            o.payment_type AS payment_type,
            o.final_price AS order_total,
            COALESCE(SUM(i.paid_amount), 0) AS total_installments_paid,
            CASE
                WHEN o.payment_type = 'cash' THEN 0
                ELSE (o.final_price - COALESCE(SUM(i.paid_amount), 0))
            END AS remaining_balance,
            o.status AS payment_status
        FROM users u
        INNER JOIN carts cart ON cart.user_id = u.id
        INNER JOIN orders o ON o.cart_id = cart.id
        LEFT JOIN installments i ON i.order_id = o.id
        WHERE o.status IN ('paid', 'partially_paid')
        GROUP BY u.id, u.name, o.id, o.payment_type, o.final_price, o.status
        ORDER BY u.id, o.id;
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_statement_view');
    }
};
