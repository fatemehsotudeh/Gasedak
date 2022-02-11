<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SMSToken extends Model
{
    //
    protected $table='smstokens';
    protected $fillable = [
        'phoneNumber','smsCode','message'
    ];

    public function sendSMS()
    {
        $API_KEY=env('API_KEY');
        $sender="10004346";
        $message=$this->message;
        $receptor=$this->phoneNumber;

        try {
            $api = new \Kavenegar\KavenegarApi($API_KEY);
            $api->send($sender,$receptor,$message);
            return true;
        }catch(\Kavenegar\Exceptions\ApiException $e){
        // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
            //$e->errorMessage();
            return false;
        }catch(\Kavenegar\Exceptions\HttpException $e){
        // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
            //echo $e->errorMessage();
            return false;
        }

    }
}
