<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    //
    protected $table='ordersstatus';

    public function getStatusId($status)
    {
        return OrderStatus::where('statusName',$status)->pluck('id')[0];
    }
}
