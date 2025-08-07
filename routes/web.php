<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/check-intl', function () {
    if (extension_loaded('intl')) {
        return 'امتداد intl يعمل بنجاح!';
    } else {
        return 'امتداد intl غير مفعل.';
    }
});
