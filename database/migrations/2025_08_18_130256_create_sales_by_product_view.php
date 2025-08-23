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
        DB::statement("CREATE OR REPLACE VIEW sales_by_product_view AS
            SELECT
            customer_id,
            customer_name,
            item_id,
            item_name,
            SUM(total_quantity) AS total_quantity,
            SUM(total_value) AS total_value
FROM (
    -- المبيعات العادية من الطلبات
    SELECT
        u.id AS customer_id,
        u.name AS customer_name,
        i.id AS item_id,
        i.name AS item_name,
        SUM(oi.quantity) AS total_quantity,
        SUM(oi.price) AS total_value
    FROM users u
    JOIN carts cart ON cart.user_id = u.id
    JOIN orders o ON o.cart_id = cart.id AND o.status IN ('paid', 'partially_paid')
    JOIN order_items oi ON oi.order_id = o.id
    JOIN item_units iu ON iu.id=oi.item_unit_id
    JOIN items i ON i.id = iu.item_id
    GROUP BY u.id, u.name, i.id, i.name

    UNION ALL

    -- المبيعات من العروض الترويجية
    SELECT
        u.id AS customer_id,
        u.name AS customer_name,
        i.id AS item_id,
        i.name AS item_name,
        SUM(ooibd.quantity) AS total_quantity,
        SUM(oo.price) AS total_value
    FROM users u
    JOIN carts cart ON cart.user_id = u.id
    JOIN orders o ON o.cart_id = cart.id AND o.status IN ('paid', 'partially_paid')
    JOIN order_offers oo ON oo.order_id = o.id
    JOIN order_offer_item_batch_details ooibd ON ooibd.order_offer_id = oo.id
    JOIN purchase_receipt_items pri ON ooibd.purchase_receipt_item_id = pri.id
    JOIN items i ON pri.item_id = i.id
    GROUP BY u.id, u.name, i.id, i.name
        ) AS combined_sales
            GROUP BY customer_id, customer_name, item_id, item_name;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS sales_by_product_view");
    }
};
