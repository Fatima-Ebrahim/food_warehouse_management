<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpecialOfferRequest;
use App\Http\Requests\UpdateOfferStatusRequest;
use App\Models\SpecialOffer;
use App\Services\Orders\SpecialOfferService;
use Illuminate\Http\Request;

class SpecialOfferController extends Controller
{
    public function __construct(
        protected SpecialOfferService $specialOfferService
    ) {}

    public function create(SpecialOfferRequest $request)
    {
        $offer = $this->specialOfferService->createSpecialOffer($request->validated());

        return response()->json([
            'message' => 'Special offer created successfully.',
            'data' => $offer->load('items')
        ], 201);
    }


    public function updateOfferStatus(UpdateOfferStatusRequest $request){
        $data=$request->validated();
        $result=$this->specialOfferService->updateOfferStatus($data);
        return response()->json(["data"=>$result , 'message'=>'updated successfully']);

    }

    public function getInactiveOffers(){
        return response()->json(['data'=>$this->specialOfferService->getInactiveOffers()],200);
    }

    public function getActiveOffers(){
        return response()->json(['data'=>$this->specialOfferService->getActiveOffers()],200);
    }

    public function getAllOffers(){
        return response()->json(['data'=>$this->specialOfferService->getAllOffers()],200);
    }

    public function update(SpecialOfferRequest $request, SpecialOffer $offer)
    {
        $offer = $this->specialOfferService->updateSpecialOffer($offer, $request->validated());

        return response()->json([
            'message' => 'Special offer updated successfully.',
            'data' => $offer->load('items')
        ], 200);
    }

    public function destroy($offerId)
    {
        $this->specialOfferService->deleteSpecialOffer($offerId);

        return response()->json([
            'message' => 'Special offer deleted successfully.'
        ], 200);
    }
}
