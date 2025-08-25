<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\Costumer\CustomerRepository;
use Illuminate\Support\Facades\Hash;

class CustomerService{

    public function __construct(protected CustomerRepository $customerRepository)
    {
    }

    public function getProfile(User $user){
        return $this->customerRepository->getProfile($user);
    }
    public function updateProfile($user, array $data): array
    {
        // تحقق من كلمة السر إذا طلب تغييرها
        if (!empty($data['new_password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                return [
                    'success' => false,
                    'message' => 'كلمة السر الحالية غير صحيحة',
                ];
            }
        }

        $this->customerRepository->updateProfile($user, $data);

        return [
            'success' => true,
            'message' => 'تم تعديل البروفايل بنجاح',
        ];
    }



}
