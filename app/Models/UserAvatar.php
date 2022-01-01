<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAvatar extends Model
{
    //
    protected $table='useravatars';
    protected $fillable = [
        'userId','imagePath'
    ];
}
