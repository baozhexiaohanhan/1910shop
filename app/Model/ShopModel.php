<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ShopModel extends Model
{
    protected $table = 'shop_goods';
    //
    protected $primaryKey  = 'goods_id';
    //
    protected $guarded = [];
    //
    public $timestamps = false;
}
