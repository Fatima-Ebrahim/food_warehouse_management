<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Services\CustomerService;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(protected CustomerService $service)
    {
    }

    public function getProfile(){
        $userId = auth()->user()->id;
        return response()->json(['data'=>$this->service->getProfile($userId )]) ;
    }

    public function UpdateProfile(UpdateProfileRequest $request){
        $user = Auth::user();
        $this->service->updateProfile($user, $request->validated());
        return response()->json(['message'=>'profile updated successfully']           );

    }
    public function getCustomers(){

        $customers = $this->service->getCustomers();
        return response()->json(['customers'=>$customers]           );

    }
}
