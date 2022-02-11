<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    protected $fillable=['userId','amount','result','orderId'];

    public function instantPayment($paymentType='instant')
    {
        //Connecting to zarinpal port
        $data = $this->getRequestData($paymentType);

        if (!$this->connectToZarinpalApi($data,'https://api.zarinpal.com/pg/v4/payment/request.json')) {
            return ['status'=> 'error','message'=> 'curl error'];
        } else {
            if (empty($this->result['errors'])) {
                if ($this->result['data']['code'] == 100) {
                    $paymentLink='https://www.zarinpal.com/pg/StartPay/' . $this->result['data']["authority"];
                    return ['status'=>'success','paymentLink'=> $paymentLink];
                }
            }
            else {
                return ['status'=>'error','message'=> $this->result['errors']['message']];
            }
        }
    }

    public function getRequestData($paymentType)
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
                return "http://localhost:8000/verifyPayment/$this->amount/$this->userId/$this->orderId";
            case 'wallet':
                return "http://localhost:8000/verifyIncreaseInventory/$this->amount/$this->userId";
        }
    }

    public function connectToZarinpalApi($data,$url)
    {
        $jsonData = json_encode($data);
        $ch = curl_init($url);
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

    public function verifyPayment($request,$state)
    {
        $authority = $request->Authority;
        if ($request->cartId!=''){
            $this->orderId=$request->cartId;
        }
        $data = [
            "merchant_id" => env('merchant_id'),
            "authority" => $authority,
            "amount" => $request->amount
        ];

        if (!$this->connectToZarinpalApi($data,'https://api.zarinpal.com/pg/v4/payment/verify.json')) {
            return response()->json(['status' =>'error','message'=>'curl error'],500);
        } else {
            //save deposit transaction
            return $this->saveTransaction($request,$authority,$state);
        }
    }

    public function saveTransaction($request,$authority,$state)
    {
        $depositTransaction=new DepositTransaction();
        $depositTransaction->userId=$request->userId;
        $depositTransaction->amount=$request->amount;
        $depositTransaction->authority=$authority;

        if ($request->Status == "OK") {
            $depositTransaction->refId=$this->result['data']['ref_id'];
            $depositTransaction->transactionStatus='Transation success';

            //Check that no duplicate transactions are recorded
            if (!DepositTransaction::where('authority',$authority)->exists()){
                $depositTransaction->save();

                switch ($state){
                    case 'orderPayment':
                        $order=new Order();
                        $order->id=$this->orderId;
                        $order->userId=$this->userId;
                        $order->updateOrderStatus($depositTransaction->id);
                        $order->sendSMSToStoreForEachOrder('register');
                        $order->updateDiscountCodes();
                        $order->deleteCartAndUpdateOrder();
                        break;
                    case 'wallet':
                        $this->updateWalletAmount($request);
                        break;
                }
                return response()->json(['message'=>'Transation success.'],200);
            }
        }else{
            $depositTransaction->transactionStatus=$this->result['errors']['message'];
            //Check that no duplicate transactions are recorded
            if (!DepositTransaction::where('authority',$authority)->exists()){
                $depositTransaction->save();
            }
            return response()->json(['status' =>'error','message'=> $this->result['errors']['message']]);
        }
    }

    public function updateWalletAmount($request)
    {
        $userFound=Wallet::where('userId',$request->userId);
        $userBalance=$userFound->pluck('balance')[0];
        $balance=intval($request->amount)+$userBalance;
        $userFound->update(['balance'=>$balance]);
    }
}
