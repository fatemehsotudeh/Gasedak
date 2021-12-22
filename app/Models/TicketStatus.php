<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    //
    protected $table='ticketsstatus';
    protected $fillable = [
        'name'
    ];
}
