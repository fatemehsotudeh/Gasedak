<?php

namespace App\Libraries;



use App\Models\Store;
use App\Models\StoreAddress;
use App\Models\TicketStatus;
use Illuminate\Database\Eloquent\Model;

class Helper{
    public function generateRandomDigitsCode($length)
    {
        return rand(
            ((int) str_pad(1, $length, 0, STR_PAD_RIGHT)),
            ((int) str_pad(9, $length, 9, STR_PAD_RIGHT))
        );
    }

    public function diffDate($currentDate,$sendCodeDate)
    {
        return strtotime($currentDate) - strtotime($sendCodeDate);
    }

    public function decodeBearerToken($token)
    {
        return json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))));
    }

    public function generateAlphaNumericCode($length)
    {
        return substr(md5(uniqid(rand(), true)),null,$length);
    }

    public function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function imageSavePath($name)
    {
        return base_path()."\public\uploads\\".$name;
    }

    public function fileSavePath($name)
    {
        return  $this->imageSavePath($name);
    }

    public function maxImageSize()
    {
        return (2* pow(10,6));
    }

    public function maxFileSize()
    {
        return  $this->maxImageSize();
    }

    public function isAllowedImageType($imageType)
    {
        if ($imageType==="image/jpeg" or $imageType==="image/png" or $imageType==="image/jpg" or $imageType==="image/gif"){
            return true;
        }else{
            return false;
        }
    }

    public function isAllowedFileType($FileType)
    {
        if ($FileType==="image/jpeg" or $FileType==="image/png" or $FileType==="image/jpg" or $FileType==="image/gif" or $FileType==="application/x-rar" or $FileType==="application/zip"){
            return true;
        }else{
            return false;
        }
    }

    public function initializeTicketStatusTable()
    {
        $ticketStatus=new TicketStatus();
        $ticketStatus->name='waiting for answer';
        $ticketStatus->save();

        $ticketStatus=new TicketStatus();
        $ticketStatus->name='answered';
        $ticketStatus->save();

        $ticketStatus=new TicketStatus();
        $ticketStatus->name='closed';
        $ticketStatus->save();

    }

    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    public function splitSentence($sentence,$index)
    {
        $delimiter = ' ';
        $words = explode($delimiter, $sentence);

        return $words[$index];
    }

    public function splitSentenceAgeRange($sentence,$index)
    {
        $delimiter = '(';
        $words = explode($delimiter, $sentence);

        return $words[$index];
    }
    public function calculateUserDistanceToBookStores($userLat,$userLng)
    {
        $listLat2=StoreAddress::all()->pluck('lat','id');
        $listLng2=StoreAddress::all()->pluck('lng','id');

        $distances=[];

        foreach ($listLng2 as $key=>$value){
            $distance=$this->distance(floatval($userLat),floatval($userLng),floatval($listLat2[$key]),floatval($listLng2[$key]),'k');
            $distances[$key]=$distance;
        }

        asort($distances);

        return $distances;
    }

    public function calculateUserDistanceToBookStoresByKeyWord($userLat,$userLng,$keyWord)
    {
        $listLat2=StoreAddress::join('stores','stores.id','storesaddress.storeId')
            ->where('stores.name','like','%'.$keyWord.'%')
            ->pluck('lat','storesaddress.id');

        $listLng2=StoreAddress::join('stores','stores.id','storesaddress.storeId')
            ->where('stores.name','like','%'.$keyWord.'%')
            ->pluck('lng','storesaddress.id');

        $distances=[];

        foreach ($listLng2 as $id=>$lng){
            $distance=$this->distance(floatval($userLat),floatval($userLng),floatval($listLat2[$id]),floatval($listLng2[$id]),'k');
            $distances[$id]=$distance;
        }

        //sort the distance from the user to the bookstores based on the nearest
        asort($distances);

        return $distances;
    }
};
