<?php

namespace App\Http\Controllers;

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
}
