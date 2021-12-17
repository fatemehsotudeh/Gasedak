<?php

namespace App\Http\Controllers;

use App\Models\SMSToken;
use http\Client\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use App\Libraries;

class SmsTokenController extends Controller
{
    //
    public function requestToken(Request $request)
    {
        $phoneNumber=$request->phoneNumber;

        // Check if field is empty
        if(empty($phoneNumber) ) {
            return response()->json(['status' => 'error', 'message' => 'You must fill the phoneNumber field']);
        }

        //check phoneNumber
        if(!preg_match("/^[0-9]{11}$/", $phoneNumber)) {
            return response()->json(['status' => 'error', 'message' => 'You must provide the correct phoneNumber']);
        }

        //generate random 5 digit code
        $helper=new Libraries\Helper();
        $smsCode=$helper->generateRandomDigitsCode(5);


        //send smsCode using kavenegar api
        try {
            //need params
            $receptor =$phoneNumber ;
            $token= $smsCode;
            $template=env('template');
            $API_KEY=env('API_KEY');

            $api = new \Kavenegar\KavenegarApi($API_KEY);
            $api->VerifyLookup($receptor, $token,0,0, $template);

            //save code and phone number in table smsTokens
            $smsToken=new SMSToken();
            $smsToken->smsCode=$token;
            $smsToken->phoneNumber=$phoneNumber;

            //if phoneNumber exists update sms Code column
            //else create new
            $user=SMSToken::where('phoneNumber', '=', $phoneNumber);
            if($user->exists()) {
                $user->update(['smsCode'=>$smsToken->smsCode,'isVerified'=>false]);
                return response()->json(["message" => "Send smsCode successfully"],200);
            }else{
                $smsToken->save();
                return response()->json(["message" => "Send smsCode successfully"],200);
            }
        }
        catch(\Kavenegar\Exceptions\ApiException $e){
            // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
              return response()->json(["status" => "error","message" => $e->errorMessage()]);
        }
        catch(\Kavenegar\Exceptions\HttpException $e){
            // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
            return response()->json(["status" => "error","message" => $e->errorMessage()],500);
        }
    }

    public function validateToken(Request $request)
    {
        $phoneNumber = $request->phoneNumber;
        $code = $request->code;

        // Check if fields are empty
        if (empty($phoneNumber) or empty($code)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill the phoneNumber field']);
        }

        //check phoneNumber
        if (!preg_match("/^[0-9]{11}$/", $phoneNumber)) {
            return response()->json(['status' => 'error', 'message' => 'You must provide the correct phoneNumber']);
        }

        $userData=SMSToken::where('phoneNumber', '=', $phoneNumber);
        try{
            if ( $userData->exists()) {
                  $user = $userData->get();
                  $user = json_decode($user[0], false);

                  //diff between two datetime to check if smsCode expired or not
                  $helper=new Libraries\Helper();
                  $diff=$helper->diffDate(date('Y-m-d H:i:s'),$user->updated_at);

                  if ($diff <= 120 && $user->smsCode == $code) {
                      $userData->update(['isVerified' => true]);
                       return response()->json(["message" => "Verified user successfully"], 200);
                   } else if ($diff > 120 && $user->smsCode == $code) {
                      $userData->update(['smsCode' => 'null']);
                       return response()->json(["message" => "Code expired"], 403);
                   } else if ($user->smsCode != $code) {
                       return response()->json(["message" => "Code incorrect"], 401);
                   }

                }
             }catch (\Exception $e){
                   return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function requestResetPassToken(Request $request)
    {
        $phoneNumber=$request->phoneNumber;

        // Check if field is empty
        if(empty($phoneNumber) ) {
            return response()->json(['status' => 'error', 'message' => 'You must fill the phoneNumber field']);
        }

        //check phoneNumber
        if(!preg_match("/^[0-9]{11}$/", $phoneNumber)) {
            return response()->json(['status' => 'error', 'message' => 'You must provide the correct phoneNumber']);
        }

        //generate random 5 digit code
        $helper=new Libraries\Helper();
        $smsCode=$helper->generateRandomDigitsCode(5);

        //send smsCode using kavenegar api
        try {
            //need params
            $receptor =$phoneNumber ;
            $token= $smsCode;
            $template=env('template');
            $API_KEY=env('API_KEY');

            $api = new \Kavenegar\KavenegarApi($API_KEY);

            //save code and phone number in table smsTokens
            $smsToken=new SMSToken();
            $smsToken->smsCode=$token;
            $smsToken->phoneNumber=$phoneNumber;

            //if phoneNumber exists end sms code and update sms Code column
            //else error occur
            $userData=SMSToken::where('phoneNumber', '=', $phoneNumber);
            if($userData->exists()) {
                $api->VerifyLookup($receptor, $token,0,0, $template);
                $userData->update(['smsCode'=>$smsToken->smsCode,'isVerified'=>false]);
                return response()->json(["message" => "Send smsCode successfully"],200);
            }else{
                return response()->json(["message" => "user not exists"]);
            }
        }
        catch(\Kavenegar\Exceptions\ApiException $e){
            // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
            return response()->json(["status" => "error","message" => $e->errorMessage()]);
        }
        catch(\Kavenegar\Exceptions\HttpException $e){
            // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
            return response()->json(["status" => "error","message" => $e->errorMessage()],500);
        }
    }

}

