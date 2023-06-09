<?php

namespace App\Libraries;

use App\Models\Store;
use App\Models\StoreAddress;
use App\Models\TicketStatus;
use http\Env\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function imageSavePath($userId,$type)
    {
        $imageType=explode('/',$type);
        return base_path()."\public\uploads\\".$userId.".".$imageType[1];
    }

    public function fileSavePath($name)
    {
        return  base_path()."\public\uploads\\".$name;
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

    public function paginate($request,$collection,$perPage=10)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $offset = ($currentPage * $perPage) - $perPage;

        $currentPageResults = array_values($collection->slice($offset, $perPage)->all());

        $paginatedItems = new LengthAwarePaginator($currentPageResults, count($collection), $perPage);

        $paginatedItems->setPath($request->url());

        return $paginatedItems;
    }

    public function getCurrentDate()
    {
       // date_default_timezone_set('Asia/Tehran');
        return date('Y-m-d H:i:s');
    }

    public function shuffleAssociativeArray($array)
    {
        $sorted_array = $array;
        $shuffled_array = array();
        $keys = array_keys($sorted_array);
        shuffle($keys);

        foreach ($keys as $key)
        {
            $shuffled_array[$key] = $sorted_array[$key];
        }

        return $shuffled_array;
    }

};
