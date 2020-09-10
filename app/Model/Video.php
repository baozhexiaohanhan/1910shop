<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $table = 'shop_video';

    protected $guarded = [];

    public $timestamps = false;
}
