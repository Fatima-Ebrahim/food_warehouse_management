<?php
// app/Repository/RegisterRequestRepository.php
namespace App\Repositories;

use App\Models\RegisterRequest;

class RegisterRequestRepository
{

    public function getAll()
    {
        return RegisterRequest::where('request_status','processing')->get();
    }

    public function findById($id)
    {
        return RegisterRequest::findOrFail($id);
    }
    public function delete($id)
    {
        $data= RegisterRequest::findOrFail($id);
        $data->delete();
    }

    public function store(array $data)
    {
        return RegisterRequest::create($data);
    }

    public function updateStatus($id, array $data)
    {
        $request = RegisterRequest::query()->findOrFail($id);
        $request->update($data);
        return $request;
    }

}
