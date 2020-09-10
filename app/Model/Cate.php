<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cate extends Model
{
    protected $table = 'shop_category';
    //
    protected $primaryKey  = 'cate_id';
    //
    protected $guarded = [];
    //
    public $timestamps = false;
}
