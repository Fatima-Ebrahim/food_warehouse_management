<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stocktake;
use App\Models\User;
use App\Notifications\StocktakeRequestNotification;
use Carbon\Carbon;

class DispatchScheduledStocktakes extends Command
{
    protected $signature = 'stocktake:dispatch-scheduled';
    protected $description = 'Dispatch notifications for scheduled stocktakes that are due';

    public function handle()
    {
        $this->info('Checking for scheduled stocktakes to dispatch...');

        $stocktakes = Stocktake::where('type', 'scheduled')
            ->where('is_active', true)
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($stocktakes->isEmpty()) {
            $this->info('No scheduled stocktakes are due.');
            return;
        }

        $keepers = User::query()->where('role', 'warehouse_keeper')->get();

        foreach ($stocktakes as $stocktake) {
            foreach ($keepers as $keeper) {
                $keeper->notify(new StocktakeRequestNotification($stocktake));
            }
            $nextDate = Carbon::parse($stocktake->scheduled_at)->add(
                $stocktake->schedule_frequency,
                $stocktake->schedule_interval
            );

            $stocktake->update(['scheduled_at' => $nextDate]);

            $this->info("Dispatched stocktake ID: {$stocktake->id} and rescheduled for: " . $nextDate->toDateTimeString());
        }

        $this->info('All due scheduled stocktakes have been dispatched.');
    }
}
