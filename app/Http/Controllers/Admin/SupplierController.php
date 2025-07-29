<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequests\SupplierRequests\StoreSupplierRequest;
use App\Http\Requests\AdminRequests\SupplierRequests\UpdateSupplierRequest;
use App\Services\AdminServices\SupplierService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupplierController extends Controller
{
    protected $service;

    public function __construct(SupplierService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $suppliers = $this->service->getAllSuppliers();
        return response()->json($suppliers);
    }

    public function store(StoreSupplierRequest $request)
    {
        $supplier = $this->service->createSupplier($request->validated());
        return response()->json($supplier, 201);
    }

    public function show($id)
    {
        try {
            $supplier = $this->service->getSupplierById($id);
            return response()->json($supplier);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Supplier not found.'], 404);
        }
    }

    public function update(UpdateSupplierRequest $request, $id)
    {
        try {
            $supplier = $this->service->updateSupplier($id, $request->validated());
            return response()->json($supplier);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Supplier not found.'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->deleteSupplier($id);
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Supplier not found.'], 404);
        }
    }
}
