<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 发送验证码
Route::post('/v1/user/sendVerificationCode', 'v1\UserController@sendVerificationCode');

// 密码注册
Route::post('/v1/user/registerByPassword', 'v1\UserController@registerByPassword');
