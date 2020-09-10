<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Github extends Model
{
    protected $table="github";
    protected $primaryKey = 'uid';
    public $timestamps = false;
}
