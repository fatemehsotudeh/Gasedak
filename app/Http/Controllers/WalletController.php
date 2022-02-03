<?php

namespace App\Http\Controllers;

use App\Models\DepositTransaction;
use App\Models\Payment;
use App\Models\SMSToken;
use App\Models\Wallet;
use App\Models\WithdrawalTransaction;
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
                return response()->json(['status'=>'error','message'=>'There is no wallet registered for this user'],404);
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
                    $userData->update(['isVerified'=>true]);

                    //Connecting to zarinpal port
                    $payment=new Payment();
                    $payment->userId=$identifiedUser->id;
                    $payment->amount=$amount."0";
                    $result=$payment->instantPayment('wallet');
                    if ($result['status']=='error'){
                        return response()->json(['status' =>'error','message'=> $result['errors']['message']],500);
                    }else{
                        $userWallet=new Wallet();
                        if (!Wallet::where('userId',$identifiedUser->id)->exists()){
                            $userWallet->userId=$identifiedUser->id;
                            $userWallet->save();
                        }
                        return response()->json(['paymentLink' =>$result['paymentLink'],'message'=>'return payment link successfully'],200);
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
        $payment=new Payment();
        return $payment->verifyPayment($request,'wallet');
    }

    public function withdrawalRequest(Request $request)
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
                    $userData->update(['isVerified'=>true]);

                    $userWallet=Wallet::where('userId',$identifiedUser->id);
                    if ($userWallet->exists()){
                        //Check the user's Sheba number
                        //If it is empty, it gives an error to enter its Sheba number first
                        //Otherwise the transaction is recorded with the status pending
                        $userBankId=$userWallet->pluck('bankId')[0];
                        $userBalance=$userWallet->pluck('balance')[0];

                        //The amount of money withdrawn should not be less than 20000
                        if ($amount<20000){
                            return response()->json(['status' => 'error', 'message' => 'The amount of money withdrawn should not be less than 20000'],400);
                        }

                        //The requested amount should not more than wallet balance
                        if ($amount>$userBalance){
                            return response()->json(['status' => 'error', 'message' => 'The requested amount should not more than wallet balance'],400);
                        }

                        //if user bankId null return error
                        //else save transactions
                        if(!$userBankId){
                            return response()->json(['status' => 'error', 'message' => 'To withdraw, you need to enter your bankId'],400);
                        }else{
                            //create new transaction for withdrawal
                            $withdrawalTransaction=new WithdrawalTransaction();
                            $withdrawalTransaction->userId=$identifiedUser->id;
                            $withdrawalTransaction->bankId=$userBankId;
                            $withdrawalTransaction->amount=$amount;
                            $withdrawalTransaction->transactionStatus='pending';

                            if ($withdrawalTransaction->save()){
                                return response()->json(['message' => 'The withdrawal request has been registered and the status is pending'],200);
                            }
                        }
                    }

                }else if ($diff > 120 && $user->smsCode == $code) {
                    return response()->json(["message" => "Code expired"], 403);
                } else if ($user->smsCode != $code) {
                    return response()->json(["message" => "Code incorrect"], 401);
                }
            }
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function historyOfRequests(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        try{
            $allDeposits=DepositTransaction::where('userId',$identifiedUser->id)->get();
            $allWithdrawals=WithdrawalTransaction::where('userId',$identifiedUser->id)->get();
            return response()->json([
                'message'=>'return history of requests successfully',
                'data'=>[
                    'allDeposits' => $allDeposits,
                    'allWithdrawals' => $allWithdrawals
                ]
            ],200);
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
