<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAvatar extends Model
{
    //
    protected $table='userAvatars';
    protected $fillable = [
        'userId','imagePath'
    ];
}
