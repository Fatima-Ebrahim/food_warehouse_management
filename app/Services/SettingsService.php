<?php
namespace App\Services;

use App\Repositories\SettingsRepository;
class SettingsService{

    protected SettingsRepository $settingsRepository;
    public function __construct(SettingsRepository $settingsRepository)
    {
        $this->settingsRepository=$settingsRepository;
    }
    public function getPointsSettings(): array
    {
        return $this->settingsRepository->getPointsSettings();
    }

    public function updatePointsSettings(array $data): void
    {
        $this->settingsRepository->updatePointsSettings($data);
    }

    public function getOrdersSettings(): array
    {
        return $this->settingsRepository->getOrdersSettings();
    }

    public function updateOrdersSettings(array $data): void
    {
        $this->settingsRepository->updateOrdersSettings($data);
    }

    public function getInstallmentsSettings(): array
    {
        return $this->settingsRepository->getInstallmentsSettings();
    }

    public function updateInstallmentsSettings(array $data): void
    {
        $this->settingsRepository->updateInstallmentsSettings($data);
    }


}
