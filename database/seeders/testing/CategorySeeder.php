<?php

namespace Database\Seeders\testing;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $main = Category::create([
            'name' => 'مواد غذائية',
            'code' => 'FOOD',
            'description' => 'جميع أنواع المواد الغذائية',

        ]);

        Category::create([
            'name' => 'المعلبات',
            'code' => 'CANNED',
            'description' => 'منتجات معلبة',
            'parent_id' => $main->id,
        ]);

        Category::create([
            'name' => 'الخضروات المجمدة',
            'code' => 'FROZEN_VEG',
            'description' => 'خضروات مجمدة للتخزين طويل الأمد',
            'parent_id' => $main->id,
        ]);
    }
}
