<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('success', function ($data, $status = 200) {
            if(!is_array($data) || !isset($data['data'])){
                $data = [
                    "status_code" => $status,
                    "data" => $data
                ];
            }
            return Response::json($data, $status);
        });

        Response::macro('error', function ($message, $status = 400) {
            if(!is_array($message)){
                $message = [
                    "status_code" => $status,
                    "message" => $message
                ];
            }
            return Response::json($message, $status);
        });
    }
}
