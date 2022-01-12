<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GasedakSuggestion extends Model
{
    //
    protected $table='ghasedaksuggestions';

    protected $casts=[
        'translators'=> 'array',
        'authors'=>'array'
    ];
}
