<?php
namespace App\Services;

use App\Http\Requests\StoreCategoryRequest;
use App\Repositories\CategoryRepository;

class CategoryService{
    protected $CategoryRepository;

    public function __construct(CategoryRepository $category)
    {
        $this->CategoryRepository=$category;
    }

    public function CreateCategory(array $data){

            $category =$this->CategoryRepository->create($data);
            return [
                'data' => $category,
                'message' => 'Category created successfully'
                ];

    }

    public function getCategory(){
        $Categories= $this->CategoryRepository->getCategory();
        return $Categories->map(function ($Category) {
            $children= $this->CategoryRepository->hasChildren($Category->id);
            return [
                'id' => $Category->id,
                'name'=>$Category->name,
                'code' => $Category->code,
                'parent_id'=>$Category->parent_id,
                'type'=>'Category',
                'next'=> $children ? 'SubCategory' : 'item',
                'has_children'=>$children,
            ];
        });
    }

    public function getSubCategory($parent_id){
        $SubCategories = $this->CategoryRepository->getSubCategory($parent_id);
        return $SubCategories->map(function ($subCategory) {
           $children= $this->CategoryRepository->hasChildren($subCategory->id);
            return [
                'id' => $subCategory->id,
                'name'=>$subCategory->name,
                'code' => $subCategory->code,
                'parent_id'=>$subCategory->parent_id,
                'type'=>'SubCategory',
                'next'=> $children ? 'SubCategory' : 'item',
                'has_children'=>$children,
            ];
        });
    }

    public function getLastLevel(){
        return $this->CategoryRepository->getLastLevel();

    }
}
