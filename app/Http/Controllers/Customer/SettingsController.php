<?php

namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateInstallmentsSettingsRequest;
use App\Http\Requests\UpdateOrdersSettingsRequest;
use App\Http\Requests\UpdatePointsSettingsRequest;
use App\Services\SettingsService;

class SettingsController extends Controller
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function indexPoints()
    {
        return response()->json($this->settingsService->getPointsSettings());
    }

    public function updatePoints(UpdatePointsSettingsRequest $request)
    {
        $this->settingsService->updatePointsSettings($request->validated());

        return response()->json(['message' => 'setting updated successfully']);
    }
    public function indexOrders()
    {
        return response()->json($this->settingsService->getOrdersSettings());
    }

    public function updateOrders(UpdateOrdersSettingsRequest $request)
    {
        $this->settingsService->updateOrdersSettings($request->validated());

        return response()->json(['message' => 'setting updated successfully']);
    }
    public function indexInstallments()
    {
        return response()->json($this->settingsService->getInstallmentsSettings());
    }

    public function updateInstallments(UpdateInstallmentsSettingsRequest $request)
    {
        $this->settingsService->updateInstallmentsSettings($request->validated());

        return response()->json(['message' => 'setting updated successfully']);
    }
}
