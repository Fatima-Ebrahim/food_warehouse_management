<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Settings\PointsSettings;
use Spatie\LaravelSettings\SettingsMapper;

class PointsSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var SettingsMapper $mapper */
        $mapper = app(SettingsMapper::class);

        $mapper->save(
            PointsSettings::class,
            collect([
                'sy_lira_per_point'      => 10000, // كل نقطة تساوي 10000 ليرة
                'invoice_threshold_amount' => 50000, // إذا بلغت الفاتورة 50,000 ليرة
                'points_per_threshold'     => 2,     // تمنح 2 نقطة
            ])
        );


        $this->command->info('✅ Points settings have been seeded directly using SettingsMapper.');
    }
}
