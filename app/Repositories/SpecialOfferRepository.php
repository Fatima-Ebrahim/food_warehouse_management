<?php
namespace App\Repositories;


use App\Models\SpecialOffer;
use App\Models\SpecialOfferItem;

class SpecialOfferRepository
{
    public function create(array $data): SpecialOffer
    {
        return SpecialOffer::create($data);
    }

    public function attachItems(SpecialOffer $offer, array $items): void
    {
        foreach ($items as $item) {
                if($item['item_unit_id']===null){
                    $item['item_unit_id']=app(ItemRepository::class)->getBaseUnitId($item['item_id']);
                }
            $offer->Items()->create($item);
        }
    }

    public function getActiveOffers(){
        return SpecialOffer::where('is_valid',1)->with("Items")->get();
    }

    public function getInactiveOffers(){
        return SpecialOffer::where('is_valid',0)->with("Items")->get();
    }

    public function getAllOffers(){
        return SpecialOffer::all();
    }

     public function getOfferById($offerId){
        return SpecialOffer::query()->findOrFail($offerId);
     }

     public function getOfferWithItems($offerId){
        return SpecialOffer::query()->findOrFail($offerId)->with('Items')->get();
     }
//     public function getOfferItemsitemDetalis($offerItemId){
//        return SpecialOfferItem::query()->find($offerItemId)->
//                with(['itemUnit.item','itemUnit.unit'])->get();
//     }

     public function getOfferItems($offerId){
        return $this->getOfferById($offerId)->items()->get();
     }

     public function getOfferItemsitemDetalis($offer){
        return $offer->items()->with(['itemUnit.item','itemUnit.unit'])->get();
     }



     public function updateOfferStatus($offerId,$status){
        $offer =$this->getOfferById($offerId);
        $offer->update(['is_valid'=>$status]);
        return $offer;
     }

    public function update(SpecialOffer $offer, array $data): SpecialOffer
    {
        $offer->update($data);
        return $offer;
    }

    public function delete(SpecialOffer $offer): bool
    {
        $this->deleteItems($offer);
        return $offer->delete();
    }

    public function deleteItems(SpecialOffer $offer): void
    {
        $offer->items()->delete();
    }


}
