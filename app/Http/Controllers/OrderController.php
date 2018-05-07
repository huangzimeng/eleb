<?php

namespace App\Http\Controllers;

use App\Address;
use App\Cart;
use App\Order;
use App\Order_goods;
use App\Smssend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    //订单  添加
    public function addorder(Request $request)
    {
        /*
         * id
         * order_code
         * order_birth_time
         * order_status
         *
         * name
         * tel
         * provence
         * city
         * area
         * detail_address
         *
         * shop_id
         * shop_name
         * shop_img
         */

        //$request->address_id 地址id
        $addr = Address::find($request->address_id);
        //随机一个订单号
        $order_code = date('Ymd_H:i:s').uniqid('code');
        //查询购物车 status为0的所有商品
        $carts = Cart::where([
            ['user_id',$addr->user_id],
            ['status',0],
        ])->get();
        //查询店铺ID
        $shop_id = DB::table('goodslists')->where('id',$carts[0]->goods_name)->first();
        //计算订单总价
        $money = 0;
        foreach ($carts as $row){
            $goods = DB::table('goodslists')->find($row->goods_name);//goods_name为商品id
            $money += $goods->goods_price*$row->amount;
        }
        //使用事务
        DB::beginTransaction();//开启事务
        try{
            $order = Order::create([
                'order_code'=>$order_code,
                'order_birth_time'=>date('Y-m-d H:i:s'),
                'user_id'=>Auth::user()->id,
                'shop_id'=>$shop_id->shop_id,
                'shop_name'=>$shop_id->goods_name,
                'shop_img'=>$shop_id->goods_img,
                'order_price'=>$money,
                'name'=>$addr->name,
                'tel'=>$addr->tel,
                'provence'=>$addr->provence,
                'city'=>$addr->city,
                'area'=>$addr->area,
                'detail_address'=>$addr->detail_address,
            ]);
            foreach ($carts as $value){
                $goods = DB::table('goodslists')->find($value->goods_name);
                Order_goods::create([
                    'order_id'=>$order->id,
                    'goods_id'=>$value->goods_name,//id
                    'goods_name'=>$goods->goods_name,//名称
                    'goods_img'=>$goods->goods_img,
                    'goods_price'=>$goods->goods_price,
                    'amount'=>$value->amount,
                ]);
            }
        }catch (\Exception $e) {
            DB::rollBack();
            return ['status'=>'false',',message'=>'添加失败!'];
        }
        DB::commit();
        //发送邮件提醒商家
        $shop_id = $order->shop_id;
        $email = DB::table('store_infos')->where('id',$shop_id)->select('email')->first();
        $shop_name = DB::table('store_infos')->where('id',$shop_id)->select('shop_name')->first();
        Mail::send('email.blade',
            ['name'=>$shop_name->shop_name],
            function ($message) use ($email){
                $message->to($email->email)->subject('您有新的订单!');

            }
        );
        //发送短信提醒用户
        $users = DB::table('users')->where('id',$order->user_id)->first();
        $name = $users->username;
        $content = Smssend::Smssend($users->tel,$name);
        if ($content->Message == "OK"){
            return ["status"=> "true","message"=> "添加成功","order_id"=>$order->id];
        }

    }

    //订单  详情
    public function order(Request $request)
    {
        //订单id
        $order = Order::find($request->id);
        //从order_goods表里查询出所有商品 order_id = $order->id
        $goods_list = Order_goods::where('order_id',$order->id)->get();
        $order->order_status = $order->order_status==0?'代付款':'已付款';
        $order->goods_list = $goods_list;
        //拼接地址
        $order->order_address = $order->provence.$order->city.$order->area.$order->detail_address.$order->name.$order->tel;
        return response()->json($order);
    }

    //订单 列表
    public function orderList()
    {
        //根据登录人id查询订单表
        $orders = Order::where('user_id',Auth::user()->id)->get();
        foreach ($orders as $order){
            $goods_list = Order_goods::where('order_id',$order->id)->get();
            $order->order_status = $order->order_status==0?'代付款':'已付款';
            $order->goods_list = $goods_list;
            //拼接地址
            $order->order_address = $order->provence.$order->city.$order->area.$order->detail_address.$order->name.$order->tel;
        }
        return response()->json($orders);
    }

    //订单  支付
    public function pay()
    {
        return ["status"=> "true","message"=> "支付成功"];
    }

}
