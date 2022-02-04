<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $table='categories';

    public static function getCategoryId($title)
    {
        return Category::where('title',$title)->first()['id'];
    }
}
