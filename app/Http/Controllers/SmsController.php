<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class SmsController extends Controller
{
    //获取手机验证码
    public function sms(Request $request){
        $params = array ();

        // *** 需用户填写部分 ***

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAIYYLK5SArql22";
        $accessKeySecret = "QFY63X7oAudNP5ULCvf8Kqk2cbtG1B";

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $request->tel;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "小孟小吃店";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_133765020";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $code = mt_rand(100000,999999);
        $params['TemplateParam'] = Array (
            "code" => $code,
            //"product" => "阿里通信"
        );

        // fixme 可选: 设置发送短信流水号
        //$params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        //$params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new \App\Sms();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        // fixme 选填: 启用https
        // ,true
        );
        if ($content->Message = "OK"){
            //将验证码保存到redis中,五分钟后过期
            $tel = $request->tel;
            Redis::setex($tel.'sms',60*5,$code);
            return ['status'=>'true','message'=>'发送短信验证码成功!'];
        }else{
            return ['status'=>'false','message'=>'获取短信验证码失败!请稍后再试!'];
        }
    }
}
