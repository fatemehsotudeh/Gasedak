<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    protected $fillable=['userId','amount','result'];

    public function instantPayment()
    {
        //Connecting to zarinpal port
        $data = $this->getData('instant');

        if (!$this->connectToZarinpal($data)) {
            return response()->json(['status' =>'error','message'=>'curl error'],500);
        } else {
            if (empty($this->result['errors'])) {
                if ($this->result['data']['code'] == 100) {
                    $paymentLink='https://www.zarinpal.com/pg/StartPay/' . $this->result['data']["authority"];
                    return response()->json(['paymentLink' =>$paymentLink,'message'=>'return payment link successfully'],200);
                }
            }
            else {
                return response()->json(['status' =>'error','message'=> $this->result['errors']['message']],500);
            }
        }
    }

    public function getData($paymentType)
    {
        return [
            "merchant_id" => env('merchant_id'),
            //توی کیف پول مقدار زیر با صفر کانکت میشه
            "amount" => $this->amount,
            "callback_url" => $this->getCallbackURL($paymentType),
            "description" => $this->getRelatedDescription($paymentType)
        ];
    }

    public function getCallBackUrl($method)
    {
        switch ($method){
            case 'instant':
                return "http://localhost:8000/verifyPayment/$this->amount/$this->userId";
            case 'wallet':
                return "http://localhost:8000/verifyIncreaseInventory/$this->amount/$this->userId";
        }
    }

    public function connectToZarinpal($data)
    {
        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $this->result = json_decode($result, true, JSON_PRETTY_PRINT);
        curl_close($ch);


        echo $err;
        if (!$err){
            return true;
        }else{
            return false;
        }
    }

    public function getRelatedDescription($method)
    {
        switch ($method){
            case 'instant':
                return "پرداخت هزینه سفارش";
            case 'wallet':
                return "افزایش موجودی کیف پول";
        }
    }
}
