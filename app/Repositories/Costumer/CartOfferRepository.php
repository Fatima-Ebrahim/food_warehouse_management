<?php
namespace App\Repositories\Costumer;


use App\Models\CartOffer;

class CartOfferRepository{

    public function create(array $data){
        return CartOffer::query()->create($data);
    }

    public function delete($cartOfferId){
        $cartOffer=CartOffer::findOrFail($cartOfferId);
        return $cartOffer->delete();

    }

    public function getCartOfferById($id){
        return CartOffer::with('offer')->findOrFail($id);
    }

    public function getOfferByCartId($cartId){
        return CartOffer::query()->where('cart_id',$cartId)->
            with('offer.Items')->get();
    }

    public function update($cartOfferId , array $data)
    {
        $cartOffer=CartOffer::find($cartOfferId);
        $cartOffer->update($data);
        return $cartOffer;
    }

    public function getUserIdsWhoAddedOffer($offerId)
    {
        return CartOffer::where('offer_id', $offerId)
            ->join('carts', 'cart_offers.cart_id', '=', 'carts.id')
            ->pluck('carts.user_id')
            ->unique()
            ->values()
            ->toArray();
    }

    public function getOffersWhichHaveItem($itemId){
        return SpecialOffer::whereHas('items', function($query) use ($itemId) {
            $query->where('item_id', $itemId);
        })->with('items')->get();
    }

//    public
//


}
