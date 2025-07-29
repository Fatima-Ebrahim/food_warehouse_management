<?php
namespace App\Services;

use App\Repositories\UnitRepository;

class UnitService{
    protected $unitRepository;

    public function __construct(UnitRepository $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    public function createUnit(array $data): array
    {
            $unit = $this->unitRepository->create($data);
            return [
                'data' => $unit,
                'message' => 'Unit created successfully'
            ];

    }
    public function getUnits()
    {
            return $this->unitRepository->getAllUnits();
    }

}
