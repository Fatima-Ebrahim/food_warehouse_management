<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable=['user_id','commercial_certificate','points' ,'phone_number'];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class,'customer_id');
    }
}
