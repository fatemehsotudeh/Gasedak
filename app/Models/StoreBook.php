<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreBook extends Model
{
    //
    protected $table='storebooks';

    protected $casts=[
        'translators'=> 'array',
        'authors'=>'array'
    ];
}
