<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    //
    protected $table='usersaddress';
    protected $fillable = [
        'userId','lat','lng','province','city','postalCode','postalAddress'
    ];
}
