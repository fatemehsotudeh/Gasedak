<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteGood extends Model
{
    //
    protected $table='userfavoritegoods';
    protected $fillable = [
        'userId','bookId'
    ];
}
