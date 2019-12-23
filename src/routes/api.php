<?php
 Route::get('/', function(){
     return ['hello','this is jarwis api route']; 
 });
 Route::get('/test', function(){
     return 'this is jarwis test api route'; 
 });

Route::post('signup', 'UserController@register');
Route::post('login', 'UserController@authenticate');
Route::post('logout', 'UserController@logout');
Route::post('forgot-password', 'UserController@forgetPassword');
 