<?php

namespace App\Console\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Command;
use App\Models\Installment;
use Carbon\Carbon;
use App\Repositories\Costumer\InstallmentRepository;
class CheckInstallmentDueDates extends Command
{
    protected $signature = 'installments:check-due';
    protected $description = 'Check installments and mark overdue ones as late';
    protected InstallmentRepository $installmentRepository;

    ///todo اضافة اشعار عند تاخر دفع القسط
    public function handle()
    {
        $today = Carbon::today();

        // احصل على كل الدفعات التي تجاوزت تاريخ الاستحقاق ولم تُدفع
        $installments = Installment::where('status', 'pending')
            ->whereDate('due_date', '<', $today)
            ->get();
        $users=collect();
        foreach ($installments as $installment) {
            $installment->update([
                'status' => 'late',
                'note' => 'تجاوز تاريخ الاستحقاق ولم يتم الدفع',
            ]);
            $users->push(['user_id'=>$this->installmentRepository->getInstallmentOwner($installment)]);
        }

        $this->info("تم تحديث " . $installments->count() . " دفعة إلى 'late'");
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->command($this->signature)->daily();
    }
}
