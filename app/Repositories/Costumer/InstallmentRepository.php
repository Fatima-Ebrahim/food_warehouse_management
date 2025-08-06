<?php
namespace App\Repositories\Costumer;

use App\Models\Customer;
use App\Models\Installment;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InstallmentRepository{

    public function create($data){
        return Installment::create($data);
    }

    public function getOrderInstallments($order_id){
        return Installment::query()->where('order_id',$order_id);
    }

    public function isFullyPaid($order_id){
        return Installment::query()->where('order_id',$order_id)
            ->where('remaining_amount',"=",'0')->exists();
    }



    public function getUnpaidInstallmentsByUser(User $user)
    {
        return $user->cart()
            ->with(['orders.installments' => function ($query) {
                $query->where('status', 'pending');
            }])
            ->first()
            ->orders
            ->flatMap(function ($order) {
                return $order->installments;
            });
    }

    public function createNextInstallment(array $data)
    {
        return Installment::create([
            'order_id'         => $data['order_id'],
            'paid_amount'      => 0,
            'remaining_amount' => $data['remaining_amount'],
            'due_date'         => $data['due_date'],
            'status'           => 'pending',
        ]);
    }

    public function updateInstallment(Installment $installment, float $paidAmount, ?string $note = null)
    {
        $remaining = $installment->remaining_amount;
        $newRemaining = max($remaining - $paidAmount, 0);

        $installment->update([
            'paid_amount'      => $paidAmount,
            'remaining_amount' => $newRemaining,
            'paid_at'          => now(),
            'status'           => $newRemaining > 0 ? 'pending' : 'paid',
            'note'             => $note,
        ]);

        return $installment;
    }

    public function markLateInstallments()
    {
        return Installment::query()
            ->where('due_date', '<', Carbon::today())
            ->where('status', '!=', 'paid')
            ->update(['status' => 'late']);
    }

    public function getByOrder(int $orderId)
    {
        return Installment::where('order_id', $orderId)->orderBy('due_date')->get();
    }

    public function isFirstInstallment(int $orderId): bool
    {
        return !Installment::where('order_id', $orderId)->exists();
    }

    public function getNextDueInstallment(int $orderId): ?Installment
    {
        return Installment::where('order_id', $orderId)
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->first();
    }

//    public function isFullyPaid(int $orderId): bool
//    {
//        return !Installment::where('order_id', $orderId)
//            ->where('status', '!=', 'paid')
//            ->exists();
//    }

}
