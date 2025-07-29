<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisterRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'request_status',
        'commercial_certificate',
        'email_verified_at',
    ];
//
//    protected $casts = [
//        'email_verified_at' => 'datetime',
//    ];
}
