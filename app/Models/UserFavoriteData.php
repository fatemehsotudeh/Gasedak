<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteData extends Model
{
    //
    protected $table='usersfavoritedata';
    protected $fillable = [
        'userId','studyAmount','bookType','importantThing','howToBuy'
    ];

    public function getUserFavoriteDataV2()
    {
        $userFavoriteData=UserFavoriteData::where('userId',$this->userId);
        if ($userFavoriteData->exists()){
            return $userFavoriteData->first();
        }else{
            return [];
        }
    }

    public static function getUserFavoriteData($userId)
    {
        $userFavorite=UserFavoriteData::where('userId',$userId);
        if ($userFavorite->exists()){
            return $userFavorite->first();
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
