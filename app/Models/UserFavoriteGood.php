<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteGood extends Model
{
    //
    protected $table='userfavoritegoods';
    protected $fillable = [
        'userId','bookId'
    ];

    protected $casts=[
        'translators'=> 'array',
        'authors'=>'array'
    ];

    public function isbookInFavList()
    {
        $FavBook=UserFavoriteGood::where([
            ['userId',$this->userId],
            ['bookId',$this->bookId]
        ]);

        if ($FavBook->exists()){
            return 1;
        }else{
            return 0;
        }
    }
}
