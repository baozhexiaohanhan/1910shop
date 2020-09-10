<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IndexUser extends Model
{
    protected $table = 'index_user';
    protected $primaryKey = 'id';
    // 关闭时间戳
    public $timestamps = false;
    //protected $fillable = ['user_name','email','password','reg_time'];
    protected $guarded = ['passwords'];
    public static function generateToken($uid)
    {
        $str =  $uid . Str::random(5) . time() . mt_rand(1111,9999999);
        return strtoupper(substr(Str::random(5) . md5($str),1,20));
    }

}
