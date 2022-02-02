<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreAddress extends Model
{
    //
    protected $table='storesaddress';

    protected $hidden = [
        'password','email','username','IBAN'
    ];
}
