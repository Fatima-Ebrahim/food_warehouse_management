<?php

namespace Database\Seeders;

use App\Settings\OrderSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\LaravelSettings\SettingsMapper;

class OrderSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var SettingsMapper $mapper */
        $mapper = app(SettingsMapper::class);

        $mapper->save(
            OrderSettings::class,
            collect([
                'auto_cancel_delayed_orders' => true,
                'delayed_days_limit' => 2,
                'notify_on_delay' => true,
                'days_to_sent_notification'=>1
            ])
        );

        $this->command->info('✅ Order settings have been seeded successfully.');

    }
}
