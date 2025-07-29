<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected  $CategoryService;
    public function __construct(CategoryService $category)
    {
        $this->CategoryService = $category;

    }

    public function index()
    {
        return $this->CategoryService->getCategory();
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        $result = $this->CategoryService->createCategory($validated);

            return response()->json($result, 201);
    }

    public function show( $parent_id)
    {
        return $this->CategoryService->getSubCategory($parent_id);
    }

    public function showLastLevel(){
        return response()->json( $this->CategoryService->getLastLevel());
    }



//
//    public function update(UpdateWarehouseRequest $request, int $id)
//    {
//        return $this->service->updateWarehouse($id, $request->validated())
//            ->response();
//    }
//
//    public function destroy(int $id)
//    {
//        return $this->service->deleteWarehouse($id);
//    }


}
