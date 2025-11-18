<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    protected $fillable = ['reservation_id','user_id','location','action','result','ip'];
}