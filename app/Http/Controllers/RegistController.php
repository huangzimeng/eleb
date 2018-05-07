<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class RegistController extends Controller
{
    //注册
    public function regist(Request $request){
        /*
         * username: 用户名
         * tel: 手机号
         * sms: 短信验证码
         * password: 密码
         */
        //验证表单数据
        $validator = Validator::make($request->all(),
            [
                'username'=>'required|unique:users',
                'tel'=>'required|unique:users',
                'password'=>'required|min:6',
                'sms'=>'required'
            ],[
                'username.unique'=>'用户名已经存在!',
                'tel.unique'=>'电话号码已经存在!',
                'password.min'=>'密码不能少于6位!',
                'sms.required'=>'验证码不能为空!'
            ]);

        //验证失败,返回错误信息
        if ($validator->fails()){
            $errors = $validator->errors();//错误信息
            //返回错误信息
            return ['status'=>'false','message'=>$errors->first()];
        }
        //获取redis中的验证码
        $R_sms = Redis::get(($request->tel).'sms');
        //验证验证码是否正确
        if (($request->sms) != $R_sms){
            return ['status'=>'false','message'=>'验证码错误!'];
        }else{//成功
            User::create([
                'username'=>$request->username,
                'password'=>bcrypt($request->password),
                'tel'=>$request->tel,
            ]);
            //返回的数据
            return ['status'=>'true','message'=>'注册成功!'];
        }
    }
}
