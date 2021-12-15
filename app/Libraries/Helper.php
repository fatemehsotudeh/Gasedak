<?php

namespace App\Libraries;

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

};
