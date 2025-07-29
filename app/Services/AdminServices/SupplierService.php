<?php
namespace App\Services\AdminServices;

use App\Http\Resources\SupplierResource;
use App\Repositories\AdminRepository\SupplierRepository;

class SupplierService
{
    protected $repository;

    public function __construct(SupplierRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllSuppliers()
    {
        $suppliers = $this->repository->all();
        return SupplierResource::collection($suppliers);
    }

    public function getSupplierById($id)
    {
        $supplier = $this->repository->find($id);
        return new SupplierResource($supplier);
    }

    public function createSupplier($data)
    {
        $supplier = $this->repository->create($data);
        return new SupplierResource($supplier);
    }

    public function updateSupplier($id, $data)
    {
        $supplier = $this->repository->update($id, $data);
        return new SupplierResource($supplier);
    }

    public function deleteSupplier($id)
    {
        return $this->repository->delete($id);
    }
}
