<?php
namespace App\Repositories;
use App\Settings\InstallmentSettings;
use App\Settings\OrderSettings;
use App\Settings\PointsSettings;


class SettingsRepository{

    protected PointsSettings $points;
    protected OrderSettings $orders;
    protected InstallmentSettings $installments;



    public function __construct(PointsSettings $points
        ,OrderSettings $orders
        ,InstallmentSettings $installments)
    {
        $this->points = $points;
        $this->orders =$orders;
        $this->installments=$installments;
    }

    public function getPointsSettings(): array
    {
        return [
            'sy_lira_per_point' => $this->points->sy_lira_per_point,
            'invoice_threshold_amount'   => $this->points->invoice_threshold_amount,
            'points_per_threshold'   => $this->points->points_per_threshold,
        ];
    }

    public function updatePointsSettings(array $data): void
    {
        $this->points->fill($data)->save();
    }

    public function getOrdersSettings(): array
    {
        return [
            'auto_cancel_delayed_orders' => $this->orders->auto_cancel_delayed_orders,
            'delayed_days_limit'   => $this->orders->delayed_days_limit,
            'notify_on_delay'   => $this->orders->notify_on_delay,
            'days_to_sent_notification'=>$this->orders->days_to_sent_notification,
        ];
    }

    public function updateOrdersSettings(array $data): void
    {
        $this->orders->fill($data)->save();
    }

    public function getInstallmentsSettings(): array
    {
        return [
            'enable_installments' => $this->installments->enable_installments,
            'first_payment_percentage'   => $this->installments->first_payment_percentage,
            'minimum_payment_percentage'   => $this->installments->minimum_payment_percentage,
            'payment_interval_days' => $this->installments->payment_interval_days,
            'max_duration_days'   => $this->installments->max_duration_days,
            'enforce_amount_limit'   => $this->installments->enforce_amount_limit,
            'max_installment_amount' => $this->installments->max_installment_amount,
            'reject_if_insufficient_amount'   => $this->installments->reject_if_insufficient_amount,
//            'enforce_points_limit'   => $this->installments->enforce_points_limit,
//            'min_points_required' => $this->installments->min_points_required,
//            'reject_if_insufficient_points'   => $this->installments->reject_if_insufficient_points,
        ];
    }

    public function updateInstallmentsSettings(array $data): void
    {
        $this->installments->fill($data)->save();
    }

}

