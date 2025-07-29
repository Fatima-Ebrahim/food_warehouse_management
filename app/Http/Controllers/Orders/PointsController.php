<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Services\Orders\PointsService;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    protected $pointsService;
    public function __construct(PointsService $pointsService)
    {
        $this->pointsService=$pointsService;
    }
    public function getPoints(){
        return response()->json(['points'=>$this->pointsService->getPoints()] );
    }
    public function addPoints(){
        return $this->pointsService->addPoints(4);
    }
}
