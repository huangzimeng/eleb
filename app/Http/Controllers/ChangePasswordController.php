<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    //修改密码
    public function changepassword(Request $request){
        /**
         * oldPassword: 旧密码
         * newPassword: 新密码
         */

        //验证新密码不能小于6位
        if (!Auth::check()){
            return ['status'=>'false','message'=>'请先登录!'];
        }
        $validator = Validator::make($request->all(),
            [
                'newPassword'=>'required|min:6',
                'oldPassword'=>'required',
            ],
            [
                'newPassword.required'=>'新密码不能为空!',
                'oldPassword.required'=>'旧密码不能为空!',
                'newPassword.min'=>'新密码不能小于6位!'
            ]);
        if ($validator->fails()){
            $errors = $validator->errors();//错误信息
            //返回错误信息
            return ['status'=>'false','message'=>$errors->first()];
        }
        //验证旧密码
        $oldpassword = $request->oldPassword;
        if (!Hash::check($oldpassword,Auth::user()->password)){
            return ['status'=>'false','message'=>'旧密码错误!'];
        }else{
            DB::table('users')->where('id',Auth::user()->id)->update(['password'=>bcrypt($request->newPassword)]);
            return ['status'=>'true','message'=>'修改成功!'];
        }
    }
    //忘记密码
    public function forgetpassword(Request $request){
        /*
         * tel: 手机号
         * sms: 短信验证码
         * password: 密码
         */
        $validator = Validator::make($request->all(),
            [
                'tel' => ['required',"regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$/"],
                'password'=>'required|min:6',
                'sms'=>'required',
            ],
            [
                'tel.required'=>'手机号码不能为空!',
                'tel.regex'=>'手机格式不正确',
                'password.required'=>'密码不能为空!',
                'password.min'=>'密码不能少于6位!',
                'sms.required'=>'验证码不能为空!',
            ]);
        //手机格式不正确
        if ($validator->fails()){
            return ['status'=>'false','message'=>'手机号码格式不正确!'];
        }
        //验证电话号码是否存在
        $is_tel = DB::table('users')->where('tel',$request->tel)->count();
        if ($is_tel){//存在
            //判断验证码是否正确
            $R_sms = Redis::get($request->tel."sms");
            if ($R_sms == $request->sms){//验证码填写正确
                DB::table('users')->where('tel',$request->tel)->update(['password'=>bcrypt($request->password)]);
                return ['status'=>'true','message'=>'修改成功!'];
            }else{
                return ['status'=>'false','message'=>'验证码填写错误!'];
            }
        }else{//不存在
            return ['status'=>'false','message'=>'手机号码没有注册!请更换已注册手机号!'];
        }
    }
}
