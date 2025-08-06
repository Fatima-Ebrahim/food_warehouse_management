<?php

namespace App\Console\Commands;

use App\Services\AdminServices\LowStockService;
use Illuminate\Console\Command;

class CheckLowStockCommand extends Command
{
    protected $signature = 'stock:check-low';
    protected $description = 'Check for items that have reached their reorder level and notify the manager.';

    protected $lowStockService;

    public function __construct(LowStockService $lowStockService)
    {
        parent::__construct();
        $this->lowStockService = $lowStockService;
    }

    public function handle()
    {
        $this->info('Starting low stock check...');
        $this->lowStockService->processLowStockItemsAndNotify();
        $this->info('Low stock check completed successfully.');
    }
}
