<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopsController extends Controller
{
    //商家列表api
    public function shops(){
        $shops = DB::table('store_infos')->get();
        $shops->map(function ($item){
            $item->distance = 637;
            $item->estimate_time = 12;
        });
        return $shops;
    }
    //商家详细信息api
    public function shop(Request $request){
        //获取店铺id
        $id = $request->id;
        //店铺信息
        $store_info = DB::table('store_infos')->find($id);
        //店铺下的菜品分类
        $categories = DB::table('goodscategories')->where('store_id',$id)->get();
        //店铺下菜品
        $commodity= [];
        $evaluate = [
            [  "user_id"=> 12344,
                "username"=>"w******k",
                "user_img"=> "http://www.homework.com/images/slider-pic4.jpeg",
                "time"=>"2017-2-22",
                "evaluate_code"=> 1,
                "send_time"=>30,
                "evaluate_details"=> "不怎么好吃"],
            [
                "user_id"=> 12344,
                "username"=> "w******k",
                "user_img"=> "http://www.homework.com/images/slider-pic4.jpeg",
                "time"=> "2017-2-22",
                "evaluate_code"=> 4.5,
                "send_time"=> 30,
                "evaluate_details"=> "很好吃"
            ]
        ];
        $store_info->evaluate = $evaluate;
        foreach ($categories as $category){
            $category->goods_list = [];
            $goods_list = DB::table('goodslists')->where('goods_category_id',$category->id)->where('shop_id',$id)->get();
            foreach ($goods_list as $goods){
                $category->goods_list[] = $goods;
            }
           $commodity[] = $category;
        }
        $store_info->commodity =$commodity;
        return response()->json($store_info);
    }
}
