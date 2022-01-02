<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentSearch extends Model
{
    //
    protected $table='recentsearches';
    protected $fillable = [
        'userId','keyWord'
    ];
}
