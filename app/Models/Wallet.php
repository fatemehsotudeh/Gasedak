<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    //
    protected $table='wallets';
    protected $fillable = [
        'userId','balance','bankId'
    ];
}
