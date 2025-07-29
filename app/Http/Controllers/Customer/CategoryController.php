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

    public function getAllCategories()
    {
        return $this->CategoryService->getCategory();
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();
        $category = $this->CategoryService->createCategory($validated);
            return response()->json([
                    'data' => $category,
                    'message' => 'Category created successfully'
                ], 201);
    }

    public function getSubCategories( $parent_id)
    {
        return $this->CategoryService->getSubCategory($parent_id);
    }

    public function showLastLevel(){
        return response()->json( $this->CategoryService->getLastLevel());
    }





}
