<?php

namespace App\Repositories\Costumer;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class CustomerRepository{

    public function getProfile(User $user){
        return $user->with('customer')->get();
    }

    public function updateProfile(User $user, array $data)
    {
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        if (isset($data['customer']['phone_number'])) {
            $user->customer->phone_number = $data['customer']['phone_number'];
            $user->customer->save();
        }

        if (!empty($data['new_password'])) {
            $user->password = Hash::make($data['new_password']);
        }

        $user->save();

        return $user;
    }


}
