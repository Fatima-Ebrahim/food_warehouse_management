<?php

namespace App\Repositories;

use App\Models\Unit;

class UnitRepository
{

    public function create(array $data)
    {
        return Unit::query()->create($data);
    }

    public function getById(int $id)
    {
        return Unit::query()->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $unit = $this->getById($id);
        return $unit ? $unit->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $unit = $this->getById($id);
        return $unit ? $unit->delete() : false;
    }

    public function getAllUnits()
    {
        return Unit::query()->latest()->get();
    }

}
