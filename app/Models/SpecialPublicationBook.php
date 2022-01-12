<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialPublicationBook extends Model
{
    //
    protected $table='specialpublicationbooks';

    protected $casts=[
        'translators'=> 'array',
        'authors'=>'array'
    ];

}
