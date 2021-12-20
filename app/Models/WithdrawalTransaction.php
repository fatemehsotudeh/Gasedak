<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalTransaction extends Model
{
    //
    protected $table='withdrawaltransactions';
    protected $fillable = [
        'userId','amount','bankId','transactionStatus'
    ];
}
