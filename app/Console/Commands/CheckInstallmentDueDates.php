<?php

namespace App\Console\Commands;

use App\Notifications\InstallmentLateNotification;
use Illuminate\Console\Command;
use App\Models\Installment;
use Carbon\Carbon;
use App\Repositories\Costumer\InstallmentRepository;

class CheckInstallmentDueDates extends Command
{
    protected $signature = 'installments:check-due';
    protected $description = 'Check installments and mark overdue ones as late';
    protected InstallmentRepository $installmentRepository;

    public function __construct(InstallmentRepository $installmentRepository)
    {
        parent::__construct();
        $this->installmentRepository = $installmentRepository;
    }

    public function handle()
    {
        $today = Carbon::today();

        $installments = Installment::where('status', 'pending')
            ->whereDate('due_date', '<', $today)
            ->get();

        if ($installments->isEmpty()) {
            $this->info("لا توجد أقساط متأخرة ليتم تحديثها.");
            return;
        }

        $notifiedUsersCount = 0;

        foreach ($installments as $installment) {
            $installment->update([
                'status' => 'late',
                'note' => 'تجاوز تاريخ الاستحقاق ولم يتم الدفع',
            ]);

            $user = $this->installmentRepository->getInstallmentOwner($installment);

            if ($user) {
                $user->notify(new InstallmentLateNotification($installment));
                $notifiedUsersCount++;
            }
        }

        $this->info("تم تحديث " . $installments->count() . " دفعة إلى 'late'");
        $this->info("تم إرسال إشعارات إلى " . $notifiedUsersCount . " مستخدم.");
    }
}
