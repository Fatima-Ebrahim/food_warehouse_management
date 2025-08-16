<?php
namespace App\Services\Orders;

use App\Http\Resources\ShowSpecialOfferResource;
use App\Models\SpecialOffer;
use App\Models\User;
use App\Notifications\NewSpecialOfferNotification;
use App\Repositories\Costumer\CartOfferRepository;
use App\Repositories\SpecialOfferRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;

class SpecialOfferService{

    public function __construct(protected SpecialOfferRepository $offerRepository ,
                                protected CartOfferRepository $cartOfferRepository)
    {
    }

    public function createSpecialOffer(array $data)
    {

        //todo ابعت اشعار لكل المستخدمين انو انضاف عرض جديد
        $offerData = Arr::except($data, ['items']);
        $itemsData = $data['items'];
        $offer = $this->offerRepository->create($offerData);
        $this->offerRepository->attachItems($offer, $itemsData);
        $users = User::all();
        Notification::send($users, new NewSpecialOfferNotification($offer));
        return $offer;
    }

    public function updateOfferStatus(array $data){
        //todo ابعت اشعار للاشخاص يليضايفين الاوردر للسلة لما تتغير حالة الطلب
        //اذا تغيرت الحالة لغير فعال بتم حذفها من السلة عند كل المستخدمين
        $userIds=$this->cartOfferRepository->getUserIdsWhoAddedOffer($data['offerId']);
        $offer=$this->offerRepository->updateOfferStatus($data['offerId'],$data['status']);
        return ["data"=>$offer,
                "status"=>true];

    }

    public function getInactiveOffers(){
        $InactiveOffers =$this->offerRepository->getInactiveOffers();
        return ShowSpecialOfferResource::collection($InactiveOffers);
    }

    public function getActiveOffers(){
        $ValidOffers=$this->offerRepository->getActiveOffers();
        return  ShowSpecialOfferResource::collection($ValidOffers);
    }

    public function getAllOffers(){
        $AllOffers= $this->offerRepository->getAllOffers();
        return ShowSpecialOfferResource::collection($AllOffers);
    }

    public function updateSpecialOffer(SpecialOffer $offer, array $data): SpecialOffer
    {
        $offerData = Arr::except($data, ['items']);
        $itemsData = $data['items'];

        // تحديث بيانات العرض
        $this->offerRepository->update($offer, $offerData);

        // حذف العناصر القديمة وإضافة الجديدة
        $this->offerRepository->deleteItems($offer);
        $this->offerRepository->attachItems($offer, $itemsData);

        return $offer;
    }

    public function deleteSpecialOffer($offerId): bool
    {
        $offer =$this->offerRepository->getOfferById($offerId);
        return $this->offerRepository->delete($offer);
    }



}
