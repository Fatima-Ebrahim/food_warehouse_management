<?php
namespace App\Services;

use App\Http\Requests\StoreCategoryRequest;
use App\Repositories\CategoryRepository;
use App\Repositories\ItemRepository;
use Nette\Utils\ArrayList;
use PhpParser\Node\Expr\Array_;
use phpseclib3\Math\BigInteger\Engines\PHP\Reductions\Barrett;

class CategoryService{
    public function __construct(protected CategoryRepository $CategoryRepository ,
                                protected ItemRepository $itemRepository ,)
    {
    }

    public function CreateCategory(array $data)
    {
           return $this->CategoryRepository->create($data);
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

    public function getAllCategoriesWithChildAndItems(){

        $Categories= $this->CategoryRepository->getCategory();
            $collect =collect();
            foreach ($Categories as $category){
                $collect->push([
                        'id'=>$category->id ,
                        'name'=> $category->name ,
                        'child'=> $this->getAllSubsCategoryForId($category->id)]
                );
            }
            return $collect ;
    }

    public function getAllSubsCategoryForId($parent_id) {

        $SubCategories = $this->CategoryRepository->getSubCategory($parent_id);

        return $SubCategories->map(function ($subCategory) {
            $children = $this->CategoryRepository->hasChildren($subCategory->id);
            $childrenData = $children ? $this->getAllSubsCategoryForId($subCategory->id) : collect([]);
            if(!$children) {
                $items= $this->itemRepository->getItemsInCategory($subCategory->id);
                return['id' => $subCategory->id,
                'name' => $subCategory->name,
                'code' => $subCategory->code,
                'parent_id' => $subCategory->parent_id,
                'child' => $items ];// استخدم all() بدلاً من toArray()
            }

            return [
                'id' => $subCategory->id,
                'name' => $subCategory->name,
                'code' => $subCategory->code,
                'parent_id' => $subCategory->parent_id,
                'child' => $childrenData->all() // استخدم all() بدلاً من toArray()
            ];
        });
    }

    public function getLastLevelForCat($catId) {
        $data = collect();
        $SubCategories = $this->CategoryRepository->getSubCategory($catId);
        if($SubCategories->isEmpty()){
            $data->push($this->CategoryRepository->getCategoryById($catId));
        }


        $SubCategories->each(function ($subCategory) use (&$data) {
            $children = $this->CategoryRepository->hasChildren($subCategory->id);

            if (!$children) {
                $data->push($subCategory);
            } else {
                $childResults = $this->getLastLevelForCat($subCategory->id);
                $data = $data->merge($childResults);
            }
        });

        return $data;
    }
}
