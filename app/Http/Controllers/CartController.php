<?php

namespace App\Http\Controllers;

use App\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    //登录后才能操作
    public function __construct()
    {
        if (!Auth::check()){
            return ['status'=>'false','message'=>'请先登录!'];
        }
    }
    //添加  购物车
    public function addcart(Request $request){
        /**
         * goodsList: 商品列表
         * goodsCount: 商品数量
         */
        //先删除之前的购物车
        DB::delete('delete from carts where user_id=?',[Auth::user()->id]);

        $goodslist = $request->goodsList;
        $goodcount = $request->goodsCount;
        foreach ($goodslist as $key=>$goods){
            Cart::create([
                'goods_name'=>$goods,
                'amount'=>$goodcount[$key],
                'user_id'=>Auth::user()->id,
                'status'=>0
            ]);
        }
        return ['status'=>'true','message'=>'添加成功!'];
    }
    //生成订单
    public function cart(Request $request)
    {
        $goods_list = [];
        $totalCost = '';
        $carts = DB::table('carts')->where('user_id', Auth::user()->id)->get();
        foreach ($carts as $goods) {
            $goodsinfo = DB::table('goodslists')->where('id', $goods->goods_name)->select('goods_name', 'goods_img', 'goods_price')->first();
            $goods_list[] = [
                'goods_id' => $goods->id,
                'goods_name' => $goodsinfo->goods_name,
                'goods_img' => $goodsinfo->goods_img,
                'goods_price' => $goodsinfo->goods_price,
                'amount' => $goods->amount
            ];
            $totalCost += $goods->amount * $goodsinfo->goods_price;
        }

        return response()->json(['goods_list' => $goods_list, 'totalCost' => $totalCost]);
    }
}
