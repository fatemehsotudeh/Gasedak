<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositTransaction extends Model
{
    //
    protected $table='deposittransactions';
    protected $fillable = [
        'userId','amount','authority','refId','transactionStatus'
    ];

}
