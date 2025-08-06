<?php

namespace App\Http\Controllers;

use App\Settings\InstallmentSettings;
use Illuminate\Http\Request;
use App\Http\Middleware\checkInstallmentMiddleware;
class PaymentController extends Controller
{
    public function paymentMethods(){

        $collect=collect();
        $collect->add('cash');
        $installmentSetting= app(InstallmentSettings::class);
        if($installmentSetting->enable_installments){
            $collect->add('installment');
        }
        return response()->json($collect);

    }
}
