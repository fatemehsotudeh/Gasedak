<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    //
    protected $table='orderitems';
    protected $fillable=['orderId','bookId','price','discountAmount','quantity'];

}
