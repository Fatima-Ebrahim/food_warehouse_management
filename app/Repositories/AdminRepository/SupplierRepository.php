<?php

namespace App\Repositories\AdminRepository;

use App\Models\Supplier;

class SupplierRepository
{
    public function all()
    {
        return Supplier::query()->get();
    }

    public function find($id)
    {
        return Supplier::withTrashed()->findOrFail($id);
    }

    public function create($data)
    {
        return Supplier::create($data);
    }

    public function update($id, $data)
    {
        $supplier = $this->find($id);
        $supplier->update($data);
        return $supplier;
    }

    public function delete($id)
    {
        $supplier = $this->find($id);
        return $supplier->delete();
    }
}
