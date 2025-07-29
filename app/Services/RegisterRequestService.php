<?php
// app/Services/RegisterRequestService.php
namespace App\Services;

use App\Models\User;
use App\Repositories\RegisterRequestRepository;
use App\Repositories\UserRepository;
use App\Repositories\CustomerRepository;
class RegisterRequestService
{
    protected RegisterRequestRepository $repository;
    protected UserRepository $userRepository;
    protected CustomerRepository $customerRepository;
    public function __construct(RegisterRequestRepository $repository,
                                UserRepository $userRepository,
                                CustomerRepository $customerRepository)
    {
        $this->repository = $repository;
        $this->userRepository=$userRepository;
        $this->customerRepository=$customerRepository;
    }


    public function store(array $data)
    {
        // معالجة رفع الصورة
        if (isset($data['commercial_certificate'])) {
            $path = $data['commercial_certificate']->store('certificates', 'public');
            $data['commercial_certificate'] = $path;
        }

        return $this->repository->store($data);
    }
    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function getById($id)
    {
        return $this->repository->findById($id);
    }

    public function getImagePath($id)
    {
        $request = $this->repository->findById($id);
        return $request->commercial_certificate;
    }

    public function updateStatus($id, array $data)
    {
        $registerData= $this->repository->updateStatus($id, $data);
        if($data['request_status']=='accepted'){
           $user= $this->userRepository->create([
               'name' => $registerData->name,
               'email' => $registerData->email,
               'password' => '0000',
           ]);

           $this->customerRepository->create([
               'commercial_certificate'=>$registerData->commercial_certificate,
               'user_id'=>$user->id,
               'phone_number'=>$registerData->phone_number
           ]);
            return ['status '=>'doneee'];
        }
        return ['falssse'];
    }

}
