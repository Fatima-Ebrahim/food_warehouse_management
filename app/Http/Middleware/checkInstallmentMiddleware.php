<?php

namespace App\Http\Middleware;

use App\Repositories\SettingsRepository;
use App\Settings\InstallmentSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class checkInstallmentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
            $installmentSetting= app(InstallmentSettings::class);
            if(!$installmentSetting->enable_installments){
                return response()->json([
                    'message' => 'installment payment is not available now',
                ],403);
            }
        return $next($request);
    }
}
