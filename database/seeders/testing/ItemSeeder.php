<?php

namespace Database\Seeders\testing;

use App\Models\Category;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $category = Category::where('code', 'CANNED')->first();
        $unit = Unit::where('name', 'كرتونة')->first();

        Item::create([
            'name' => 'فول معلب',
            'code' => 'BEAN001',
            'description' => 'فول معلب جاهز للاستخدام',
            'category_id' => $category?->id,
            'base_unit_id' => $unit?->id,
            'minimum_stock_level' => 50,
            'maximum_stock_level' => 200,
            //'storage_conditions' => json_encode(['cool_place' => true, 'max_temp' => '25C']),
            'supplier_id' => 1,
//            'image' => 10.3,
            'Total_Available_Quantity' => 40,
            'barcode' => '1234567890123',
        ]);

        Item::create([
            'name' => 'بازلاء مجمدة',
            'code' => 'PEAS001',
            'description' => 'بازلاء خضراء مجمدة طازجة',
            'category_id' => Category::where('code', 'FROZEN_VEG')->value('id'),
            'base_unit_id' => Unit::where('name', 'كيلوغرام')->value('id'),
            'minimum_stock_level' => 100,
            'maximum_stock_level' => 500,
            //'storage_conditions' => json_encode(['freeze_required' => true, 'max_temp' => '-18C']),
            'supplier_id' => 2,

//            'image' => 0.7,
            'Total_Available_Quantity' => 10,
            'barcode' => '1234567890456',
        ]);
    }
}
