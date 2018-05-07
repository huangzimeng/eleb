<?php

namespace App\Http\Controllers;

use App\address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
//    public function __construct()
//    {
//        if (!Auth::check()){
//            return ['status'=>'false','message'=>'请先登录!'];
//        }
//
//    }
    //地址 添加
    public function add(Request $request)
    {
        /**
         * name: 收货人
         * tel: 联系方式
         * provence: 省
         * city: 市
         * area: 区
         * detail_address: 详细地址
         */

        if (!Auth::check()){
            return ['status'=>'false','message'=>'请先登录!'];
        }
        //验证手机号码是否正确
        if (preg_match("/^1[34578]\d{9}$/", $request->tel)) {
            Address::create([
                'name' => $request->name,
                'tel' => $request->tel,
                'provence' => $request->provence,
                'city' => $request->city,
                'area' => $request->area,
                'user_id' => Auth::user()->id,
                'detail_address' => $request->detail_address,
            ]);
            return ['status' => 'true', 'message' => '添加成功!'];
        } else {//正确
            return ['status' => 'false', 'message' => '手机号码格式不正确!'];
        }
    }

    //地址 列表
    public function addlist()
    {
        if (!Auth::check()){
            return ['status'=>'false','message'=>'请先登录!'];
        }
        $addlist = Address::where('user_id', Auth::user()->id)->get();
        return response()->json($addlist);
    }

    //地址 修改
    public function edit(Request $request) {
        if (!Auth::check()){
            return ['status'=>'false','message'=>'请先登录!'];
        }
        $id = $request->id;
        $one = Address::find($id);
        return response()->json($one);
    }

    //地址  保存
    public function editsave(Request $request)
    {
        if (!Auth::check()){
            return ['status'=>'false','message'=>'请先登录!'];
        }
        $id = $request->id;
        if (preg_match("/^1[34578]\d{9}$/", $request->tel)) {
            DB::table('addresses')->where('id', $id)->update([
                'name' => $request->name,
                'tel' => $request->tel,
                'provence' => $request->provence,
                'city' => $request->city,
                'area' => $request->area,
                'detail_address' => $request->detail_address,
            ]);
            return ['status' => 'true', 'message' => '修改成功!'];
        } else {//正确
            return ['status' => 'false', 'message' => '手机号码格式不正确!'];
        }
    }

    //地址  删除
    public function delete(Request $request)
    {
        if (!Auth::check()){
            return ['status'=>'false','message'=>'请先登录!'];
        }
        $id = $request->id;
        $address = Address::find($id);
        if ($address->delete()) {
            return ['status' => 'true', 'message' => '删除成功!'];
        } else {
            return ['status' => 'true', 'message' => '删除失败!'];
        }
    }

}
