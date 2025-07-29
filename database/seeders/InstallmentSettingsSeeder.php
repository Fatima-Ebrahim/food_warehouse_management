<?php

namespace Database\Seeders;

use App\Settings\InstallmentSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\LaravelSettings\SettingsMapper;

class InstallmentSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var SettingsMapper $mapper */
        $mapper = app(SettingsMapper::class);

        $mapper->save(
            InstallmentSettings::class,
            collect([
                'enable_installments' => true,
                'first_payment_percentage' => 0.3,
                'minimum_payment_percentage' => 0.1,
                'payment_interval_days' => 30,
                'max_duration_days' => 180,
                'enforce_amount_limit' => true,
                'max_installment_amount' => 1000000,
                'reject_if_insufficient_amount' => true,
                'enforce_points_limit' => true,
                'min_points_required' => 20,
                'reject_if_insufficient_points' => false,
            ])
        );

        $this->command->info('✅ Installment settings seeded successfully.');
    }
}
