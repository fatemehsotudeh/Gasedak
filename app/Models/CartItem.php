<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    //
    protected $table='cartitems';
    protected $casts=[
        'translators'=> 'array',
        'authors'=>'array'
    ];
}
