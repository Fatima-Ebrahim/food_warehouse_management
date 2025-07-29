<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Category extends Model
{
//تبعي
    use SoftDeletes;
    //في لسا تبع ال IS_ACTIVE  ما بعرف وضعها
    protected $fillable=['id','name','code','parent_id'];
    protected $casts = [
        'deleted_at' => 'datetime',
    ];
    protected $hidden=['updated_at','created_at'];
    public function parent(){
        return $this->hasOne(Category::class,"parent_id" );
    }
    public function items(){
        return $this->hasMany(Item::class,"category_id");
    }


}
