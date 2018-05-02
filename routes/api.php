<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//获取手机验证码接口
Route::get('/sms','SmsController@sms');
//用户注册
Route::post('/regist',"RegistController@regist");
//用户登录
Route::post('/login','LoginController@check');
//密码操作路由
Route::post('/changePassword','ChangePasswordController@changepassword');
Route::post('/forgetPassword','ChangePasswordController@forgetpassword');

//地址
Route::post('/addAddress','AddressController@add');
Route::get('/addressList','AddressController@addlist');
Route::get('/address','AddressController@edit');
Route::post('/editAddress','AddressController@editsave');
Route::get('/deleteAddress','AddressController@delete');

//购物车
Route::post('/addCart','CartController@addcart');
Route::get('/cart','CartController@cart');

//订单
Route::post('/addorder','OrderController@addorder');
Route::get('/order','OrderController@order');
Route::get('/orderList','OrderController@orderList');
//支付
Route::post('/pay','OrderController@pay');