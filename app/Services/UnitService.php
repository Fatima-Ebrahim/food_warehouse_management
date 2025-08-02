<?php
namespace App\Services;

use App\Repositories\UnitRepository;

class UnitService{
    protected $unitRepository;

    public function __construct(UnitRepository $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    public function createUnit(array $data)
    {
            return  $this->unitRepository->create($data);
    }
    public function getUnits()
    {
            return $this->unitRepository->getAllUnits();
    }

}
