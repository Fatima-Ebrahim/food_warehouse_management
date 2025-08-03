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
        $id=auth()->user()->id;
        return response()->json(['points'=>$this->pointsService->getPoints($id)] );
    }
    public function addPoints(){
        ///todo "use the api on real conditions"
        $id=auth()->user()->id;
         $this->pointsService->addPoints(4, $id);
    }
}
