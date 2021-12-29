<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteData extends Model
{
    //
    protected $table='usersfavoritedata';
    protected $fillable = [
        'studyAmount','bookType','importantThing','howToBuy'
    ];
}
