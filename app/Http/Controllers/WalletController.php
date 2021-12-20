<?php

namespace App\Http\Controllers;

use App\Models\DepositTransaction;
use App\Models\SMSToken;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use App\Libraries;


class WalletController extends Controller
{
    //
    public function getWalletData(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        try {
            //if wallet for this user id exists return wallet data
            //else error

            $userWallet=Wallet::where('userId',$identifiedUser->id);
            if ($userWallet->exists()){
                return response()->json(['data'=> $userWallet->get()[0] ,'message'=> 'get wallet data successfully'],200);
            }else{
                return response()->json(['status'=>'error','message'=>'There is no wallet registered for this user']);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function updateBankId(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $bankId=$request->bankId;

        // Check if field empty
        if (empty($bankId)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill bankId field']);
        }

        try {
            //if wallet for this user id exists updated bankId
            //else save wallet for this user

            $userBankId=Wallet::where('userId',$identifiedUser->id);
            if ($userBankId->exists()){
                $userBankId->update(['bankId'=>$bankId]);
                return response()->json(['message'=>'updated bankId successfully'],200);
            }else{
                //create new wallet
                $wallet=new Wallet();
                $wallet->userId=$identifiedUser->id;
                $wallet->bankId=$bankId;

                $wallet->save();
                return response()->json(['message'=>'insert bankId successfully'],200);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function increaseInventory(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //posted params
        $phoneNumber = $request->phoneNumber;
        $code=$request->code;
        $amount =$request->amount;

        // Check if field is empty
        if (empty($phoneNumber) or empty($code) or empty($amount)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        //check phoneNumber
        if(!preg_match("/^[0-9]{11}$/", $phoneNumber)) {
            return response()->json(['status' => 'error', 'message' => 'You must provide the correct phoneNumber']);
        }

        //Check the entered token with the smsTokens table
        $userData=SMSToken::where('phoneNumber',$phoneNumber);
        try {
            if ($userData->exists()) {
                $user = $userData->get();
                $user = json_decode($user[0], false);

                //diff between two datetime to check if smsCode expired or not
                $diff=$helper->diffDate(date('Y-m-d H:i:s'),$user->updated_at);

                if ($diff <= 120 && $user->smsCode == $code) {
                    //isVerified become true
                    SMSToken::where('phoneNumber',$phoneNumber)->update(['isVerified'=>true]);

                    //Connecting to zarinpal port
                    $data = [
                        "merchant_id" => env('merchant_id'),
                        "amount" => $amount."0",
                        "callback_url" => "http://localhost:8000/verifyIncreaseInventory/$amount/$identifiedUser->id",
                        "description" => "افزایش موجودی کیف پول"
                    ];

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
                    $result = json_decode($result, true, JSON_PRETTY_PRINT);
                    curl_close($ch);


                    if ($err) {
                        return response()->json(['status' =>'error','message'=>'curl error']);
                    } else {
                        if (empty($result['errors'])) {
                            if ($result['data']['code'] == 100) {
                                $userWallet=new Wallet();
                                if (!Wallet::where('userId',$identifiedUser->id)->exists()){
                                    $userWallet->userId=$identifiedUser->id;
                                    $userWallet->save();
                                }
                                $paymentLink='https://www.zarinpal.com/pg/StartPay/' . $result['data']["authority"];
                                return response()->json(['paymentLink' =>$paymentLink,'message'=>'return payment link successfully'],200);
                            }
                        }
                        else {
                            return response()->json(['status' =>'error','message'=> $result['errors']['message']]);
                        }
                    }
                } else if ($diff > 120 && $user->smsCode == $code) {
                    return response()->json(["message" => "Code expired"], 403);
                } else if ($user->smsCode != $code) {
                    return response()->json(["message" => "Code incorrect"], 401);
                }
            }
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function verifyIncreaseInventory(Request $request)
    {
        $authority = $_GET['Authority'];
        $data = [
            "merchant_id" => env('merchant_id'),
            "authority" => $authority,
            "amount" => $request->amount."0"
        ];

        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        $result = json_decode($result, true);


        if ($err) {
            return response()->json(['status' =>'error','message'=>'curl error']);
        } else {
            //save deposit transaction
            $depositTransaction=new DepositTransaction();
            $depositTransaction->userId=$request->userId;
            $depositTransaction->amount=$request->amount;
            $depositTransaction->authority=$authority;

            if ($_GET['Status'] == "OK") {
                $depositTransaction->refId=$result['data']['ref_id'];
                $depositTransaction->transactionStatus='Transation success';

                //Check that no duplicate transactions are recorded
                if (!DepositTransaction::where('authority',$authority)->exists()){
                    $depositTransaction->save();

                    //Because the transaction is successful, the wallet balance increases
                    $userFound=Wallet::where('userId',$request->userId);
                    $userBalance=$userFound->pluck('balance')[0];
                    $balance=intval($request->amount)+$userBalance;
                    $userFound->update(['balance'=>$balance]);

                    return response()->json(['message'=>'Transation success.'],200);
                }
            }else{
                $depositTransaction->transactionStatus=$result['errors']['message'];

                //Check that no duplicate transactions are recorded
                if (!DepositTransaction::where('authority',$authority)->exists()){
                    $depositTransaction->save();
                }

                return response()->json(['status' =>'error','message'=> $result['errors']['message']]);
            }
        }
    }
}
