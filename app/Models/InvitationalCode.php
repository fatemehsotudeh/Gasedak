<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationalCode extends Model
{
    //
    protected $table='invitationalcodes';

    //casts used by from json to array
    protected $casts=[
        'usedBy'=> 'array'
    ];

    protected $fillable = [
        'userId','invitationalCode','smsCode'
    ];
}
