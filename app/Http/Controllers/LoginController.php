<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    //验证用户登录
    public function check(Request $request){
        /**
         * name:用户名
         * password:密码
         */
        if (Auth::attempt(['username'=>$request->name,'password'=>$request->password,'status'=>1])){
            //登录验证成功!
            return ['status'=>'true','message'=>'登录成功!','user_id'=>Auth::user()->id,'username'=>Auth::user()->username];
        }else{
            //登录验证失败!
            return ['status'=>'false','message'=>'登录失败!用户名或密码错误!','user_id'=>'','username'=>''];
        }
    }
}
