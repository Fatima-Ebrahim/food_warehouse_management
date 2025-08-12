<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW item_inventory_details_view AS
            SELECT
                items.id AS item_id,
                items.name AS item_name,
                items.minimum_stock_level,
                COALESCE(SUM(pri.available_quantity), 0) AS total_quantity_in_base_unit
            FROM
                items
            LEFT JOIN
                purchase_receipt_items AS pri ON items.id = pri.item_id
            GROUP BY
                items.id,
                items.name,
                items.minimum_stock_level;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS item_inventory_details_view");
    }
};
