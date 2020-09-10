<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\ShopGoods;

class GoodsController extends Controller
{
    //
    public function home()
    {
        $list = ShopGoods::select('goods_id','goods_name','goods_img','goods_price')->orderBy('goods_id','desc')->limit(10)->get();
        $response = [
            'errno' => 0,
            'msg'   => 'ok',
            'data'  => [
                'list'  => $list
            ]
        ];
        return $response;
    }
}
