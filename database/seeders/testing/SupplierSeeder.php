<?php

namespace Database\Seeders\testing;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::create([
            'name' => 'شركة الأغذية الوطنية',
            'phone' => '0966-11-1234567',
            'email' => 'info@nationalfoods.com',
            'address' => 'المنطقة الصناعية - دمشق',
            'type' => 'company',
        ]);

        Supplier::create([
            'name' => 'عبد الله الأحمد',
            'phone' => '0966-944-987654',
            'email' => 'abdullah@example.com',
            'address' => 'ريف دمشق - دوما',
            'type' => 'individual',
        ]);

        Supplier::create([
            'name' => 'شركة الخيرات للتوريد',
            'phone' => '0966-21-998877',
            'email' => 'contact@alkhayrat.com',
            'address' => 'حلب - المدينة الصناعية',
            'type' => 'company',
        ]);
    }
}
