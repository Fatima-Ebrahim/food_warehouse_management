<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\testing\CartItemSeeder;
use Database\Seeders\testing\CartSeeder;
use Database\Seeders\testing\CategorySeeder;
use Database\Seeders\testing\CustomerSeeder;
use Database\Seeders\testing\InstallmentsSeeder;
use Database\Seeders\testing\ItemSeeder;
use Database\Seeders\testing\ItemUnitSeeder;
use Database\Seeders\testing\OrderBatchDetailsSeeder;
use Database\Seeders\testing\OrderItemSeeder;
use Database\Seeders\testing\OrderSeeder;
use Database\Seeders\testing\PointTransactionsSeeder;
use Database\Seeders\testing\SupplierSeeder;
use Database\Seeders\testing\UnitSeeder;
use Database\Seeders\testing\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PointsSettingsSeeder::class);
        $this->call(OrderSettingsSeeder::class);
        $this->call(InstallmentSettingsSeeder::class);
        $this->call([
            CategorySeeder::class,
            SupplierSeeder::class,
            UnitSeeder::class,
            ItemSeeder::class,
            ItemUnitSeeder::class,
        ]);
        $this->call([
//            UserSeeder::class,
//            CartSeeder::class,
//            CartItemSeeder::class,
//            OrderSeeder::class,
//            OrderItemSeeder::class,
//            OrderBatchDetailsSeeder::class,
//            InstallmentsSeeder::class,
//            CustomerSeeder::class,
//            PointTransactionsSeeder::class,
        ]);


        // User::factory(10)->create();
//
//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);
    }
}
