<?php
namespace App\Repositories;


use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;

class CategoryRepository{

    public function create(array $data){
        return Category::query()->create($data);
    }
    public function getCategory(){
        return Category::query()->where('parent_id',null)->get();
    }
    public function getSubCategory($parent_id){
        return Category::query()->where('parent_id',$parent_id)->get();
    }
    public function hasChildren($id):bool
    {
        return Category::query()->where('parent_id',$id)->exists();
    }
    public function getLastLevel(){
        $lastlevel=Category::all();
        $leves=collect();
        foreach ($lastlevel as $level){
            $id=$level->id;
            if(Category::query()->where('parent_id',$id)->doesntExist())
                $leves->push($level);
        }
        return $leves;
    }


}
