<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteData extends Model
{
    //
    protected $table='usersfavoritedata';
    protected $fillable = [
        'studyAmount','bookType','importantThing','howToBuy'
    ];

    public function getUserFavoriteData()
    {
        $userFavoriteData=UserFavoriteData::where('userId',$this->userId);
        if ($userFavoriteData->exists()){
            return $userFavoriteData->first();
        }else{
            return [];
        }
    }

    public static function findKeywordFromUserAgeRange($sentence)
    {
        $delimiter = ' ';
        $words = explode($delimiter, $sentence);
        return $words[0];
    }
}
