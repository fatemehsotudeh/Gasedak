<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SMSToken extends Model
{
    //
    protected $table='smstokens';
    protected $fillable = [
        'phoneNumber','smsCode'
    ];
}
