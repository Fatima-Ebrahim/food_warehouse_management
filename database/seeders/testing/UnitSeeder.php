<?php

namespace Database\Seeders\testing;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Unit::create([
            'name' => 'كرتونة',
            'type' => 'count',

        ]);

        Unit::create([
            'name' => 'كيلوغرام',
            'type' => 'weight',

        ]);

        Unit::create([
            'name' => 'لتر',
            'type' => 'volume',

        ]);
    }
}
