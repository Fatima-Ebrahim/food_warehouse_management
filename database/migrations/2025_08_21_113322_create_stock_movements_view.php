<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW stock_movements_view AS
            -- الوارد من الموردين
            SELECT
                'incoming' AS type,
                pri.id,
                pri.created_at AS movement_date,
                pri.item_id,
                i.name AS item_name,
                i.code AS item_code ,
                pri.quantity_in_base_unit as quantity,
                pri.price,
                pri.expiry_date,
                pri.production_date,
                pri.available_quantity,
                po.receipt_number AS document_number,
                po.receipt_date,
                s.name AS partner_name,
                s.id AS partner_id,
                'supplier' AS partner_type,
                'purchase_receipt' AS source_type
            FROM purchase_receipt_items pri
            JOIN purchase_orders po ON pri.purchase_order_id = po.id
            JOIN items i ON pri.item_id = i.id
            JOIN suppliers s ON po.supplier_id = s.id

            UNION ALL

            -- الصادر للعملاء (من الطلبات العادية)
            SELECT
                'outgoing' AS type,
                obd.id AS id,
                obd.created_at AS movement_date,
                pri.item_id AS item_id,
                i.name AS item_name,
                 i.code AS item_code,
                obd.quantity,
                pri.price,
                pri.expiry_date,
                pri.production_date, -- أضيف
                pri.available_quantity,
                o.id AS document_number,
                o.created_at AS receipt_date,
                user.name AS partner_name,
                user.id AS partner_id ,
                'customer' AS partner_type,
                'order' AS source_type
            FROM order_batch_details obd
            JOIN purchase_receipt_items pri ON obd.purchase_receipt_item_id = pri.id
            JOIN items i ON pri.item_id = i.id
            JOIN order_items oi ON obd.order_item_id = oi.id
            JOIN orders o ON oi.order_id = o.id
            JOIN carts cart ON o.cart_id =cart.id
             JOIN users user ON cart.user_id = user.id

            UNION ALL

            -- الصادر للعملاء (من العروض الترويجية)
            SELECT
                'outgoing' AS type,
                ooibd.id AS id,
                 ooibd.created_at AS movement_date,
                pri.item_id AS item_id,
                i.name AS item_name,
                 i.code AS item_code,
                ooibd.quantity,
                pri.price,
                pri.expiry_date,
                pri.production_date, -- أضيف
                pri.available_quantity,
                o.id AS document_number,
                o.created_at AS receipt_date,
                user.name AS partner_name,
                user.id AS partner_id,
                'customer' AS partner_type,
                'offer' AS source_type
            FROM order_offer_item_batch_details ooibd
            JOIN purchase_receipt_items pri ON ooibd.purchase_receipt_item_id = pri.id
            JOIN items i ON pri.item_id = i.id

            JOIN order_offers oo ON ooibd.order_offer_id = oo.id
           JOIN orders o ON oo.order_id = o.id
            JOIN carts cart ON o.cart_id =cart.id
             JOIN users user ON cart.user_id = user.id
        ");
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS stock_movements_view");
    }
};
