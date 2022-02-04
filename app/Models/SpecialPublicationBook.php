<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialPublicationBook extends Model
{
    //
    protected $table='specialpublicationbooks';

    protected $hidden = [
        'password','email','username','IBAN'
    ];

    protected $casts=[
        'translators'=> 'array',
        'authors'=>'array'
    ];

}
