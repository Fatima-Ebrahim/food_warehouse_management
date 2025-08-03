<?php

namespace Database\Seeders\testing;

use App\Models\Item;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ItemUnit;
class ItemUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items=Item::all();
        $units=Unit::all();
        foreach ($items as $item){
            foreach ($units as $unit){
                ItemUnit::create([
                    'item_id'=>$item->id,
                        'unit_id'=>$unit->id,
                        'conversion_factor'=>1,
                        'is_default'=>0,
                        'selling_price'=>100
                ]);
            }
        }
    }
}
