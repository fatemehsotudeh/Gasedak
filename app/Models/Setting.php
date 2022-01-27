<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    //
    protected $table='settings';

    public function getPostCost()
    {
        return intval(Setting::where('name','postCost')->pluck('value')[0]);
    }
}
