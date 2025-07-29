<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUnitRequest;
use App\Services\UnitService;
use Illuminate\Http\Request;

class UnitContoller extends Controller
{
    protected $unitService;
    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    public function store(CreateUnitRequest $request)
    {
        $result = $this->unitService->createUnit($request->validated());
        return response()->json([
            'data' => $result,
            'message' => 'Unit created successfully'
        ],200);
    }
    public function index()
    {
        $result = $this->unitService->getUnits();
        return response()->json($result, 200);
    }

}
