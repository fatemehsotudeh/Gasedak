<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    //
    protected $table='ticketmessages';
    protected $fillable = [
        'ticketId','senderId','isAdmin','message','filePath'
    ];
}
