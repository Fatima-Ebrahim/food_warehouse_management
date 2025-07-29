<?php

namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegisterRequest;
use App\Http\Requests\UpdateRegisterRequestStatusRequest;
use App\Models\RegisterRequest;
use App\Services\RegisterRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class RegisterRequestController extends Controller
{
    protected RegisterRequestService $service;

    public function __construct(RegisterRequestService $service)
    {
        $this->service = $service;
    }

    public function store(StoreRegisterRequest $request)
    {
        $item = $this->service->store($request->validated()
            + ['commercial_certificate' => $request->file('commercial_certificate')]);
        return response()->json($item, 201);
    }
    public function index()
    {
        return response()->json($this->service->getAll());
    }

    public function show($id)
    {
        return response()->json($this->service->getById($id));
    }

    public function showCertificate($id)
    {
        $path = $this->service->getImagePath($id);

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->file(storage_path("app/public/{$path}"));
    }

    public function updateStatus(UpdateRegisterRequestStatusRequest $request, $id)
    {
        $validated = $request->validated();

        $updatedRequest = $this->service->updateStatus(
            $id,
            ['request_status' => $validated['request_status']]
        );

        return response()->json($updatedRequest);
    }

}
