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
            CREATE OR REPLACE VIEW item_total_quantity_view AS
            SELECT
                pri.item_id,
                i.name AS item_name,
                SUM(pri.available_quantity * iu.conversion_factor) AS total_quantity_in_base_unit,
                i.minimum_stock_level
            FROM
                purchase_receipt_items AS pri
            JOIN
                item_units AS iu ON pri.unit_id = iu.unit_id AND pri.item_id = iu.item_id
            JOIN
                items AS i ON pri.item_id = i.id
            GROUP BY
                pri.item_id, i.name, i.minimum_stock_level;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS item_total_quantity_view");
    }
};
