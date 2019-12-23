<?php

namespace Jarvis;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class JarvisServiceProvider extends ServiceProvider {
    
    public function boot() {
        Schema::defaultStringLength('191');
        // $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        // $this->loadViewsFrom(__DIR__ . '/resources/views', 'jarvisview');
    }
    
    public function register()
    {
        $this->registerPublishables();
    }
    
    public function registerPublishables()
    {
        $basePath = dirname(__DIR__);

        $arrayPublishable = [
            'migrations' => [
                "$basePath/src/database/migrations" => database_path('migrations')
            ],
            'seeds' => [
                "$basePath/src/database/seeds" => database_path('seeds')
            ],
            'controllers' => [
                "$basePath/src/Http/Controllers" => app_path('Api/Controllers')
            ],
            'requests' => [
                "$basePath/src/Http/Requests" => app_path('Api/Requests')
            ],
            'models' => [
                "$basePath/src/models" => app_path('Models')
            ],
            'routes' => [
                "$basePath/src/routes/api.php" => base_path('routes/api.php')
            ],
            'notification' => [
                "$basePath/src/Notifications/ForgetPasswordNotification.php" => app_path('Notifications/ForgetPasswordNotification.php')
            ],
            'ResponseMacroServiceProvider' => [
                "$basePath/src/Providers/ResponseMacroServiceProvider.php" => app_path('Providers/ResponseMacroServiceProvider.php')
            ],
            'RouteServiceProvider' => [
                "$basePath/src/Providers/RouteServiceProvider.php" => app_path('Providers/RouteServiceProvider.php')
            ],
            'FileUploadTrait' => [
                "$basePath/src/Traits/FileUploadTrait.php" => app_path('Traits/FileUploadTrait.php')
            ],
            'configAuth' => [
                "$basePath/src/config/auth.php" => config_path('auth.php')
            ]
        ];

        foreach ($arrayPublishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }
}
