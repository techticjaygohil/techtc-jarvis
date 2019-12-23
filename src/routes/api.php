<?php

$namespace = 'Jarvis\Http\Controllers';

Route::group([
    'namespace' => $namespace,
    'prefix' => 'jarvis/api',
], function () {
 Route::get('/', function(){
     return ['hello','this is jarwis api route']; 
 });
 Route::get('/test', function(){
     return 'this is jarwis test api route'; 
 });
});