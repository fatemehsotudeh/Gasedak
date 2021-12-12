<?php

namespace App\Http\Controllers;

use App\Models\SMSToken;
use http\Client\Response;
use Illuminate\Http\Request;

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
        $smsCode=rand(
            ((int) str_pad(1, 5, 0, STR_PAD_RIGHT)),
            ((int) str_pad(9, 5, 9, STR_PAD_RIGHT))
        );

        //send smsCode using kavenegar api
        try {
            //need params
            $receptor =$phoneNumber ;
            $token= $smsCode;
            $template="verifyCode";

            $API_KEY="612F4258705957766A4738334C65584B3648527663455267625672324C373162653177525646696A576E453D";

            $api = new \Kavenegar\KavenegarApi($API_KEY);
            $api->VerifyLookup($receptor, $token,0,0, $template);

            //save code and phone number in table smsTokens
            $smsToken=new SMSToken();
            $smsToken->smsCode=$token;
            $smsToken->phoneNumber=$phoneNumber;

            //if phoneNumber exit update sms Code column
            //else create new row
            if(SMSToken::where('phoneNumber', '=', $phoneNumber)->exists()) {
                SMSToken::where('phoneNumber', '=', $phoneNumber)->update(['smsCode'=>$smsToken->smsCode]);
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

        if (SMSToken::where('phoneNumber', '=', $phoneNumber)->exists()) {
              $user = SMSToken::where('phoneNumber', '=', $phoneNumber)->get();
              $user = json_decode($user[0], false);

              //diff between two datetime to check if smsCode expired or not
              $currentDate = date('Y-m-d H:i:s');//current date and time
              $sendCodeDate = $user->updated_at;//send code time
              $diff = strtotime($currentDate) - strtotime($sendCodeDate);

              if ($diff <= 120 && $user->smsCode == $code) {
                   SMSToken::where('phoneNumber', '=', $phoneNumber)->update(['isVerified' => true]);
                   return response()->json(["message" => "Verified user successfully"], 200);
               } else if ($diff > 120 && $user->smsCode == $code) {
                   SMSToken::where('phoneNumber', '=', $phoneNumber)->update(['smsCode' => 'null']);
                   return response()->json(["message" => "Code expired"], 403);
               } else if ($user->smsCode != $code) {
                   return response()->json(["message" => "Code incorrect"], 401);
               }

            }else{
                return response()->json(["message" => "An error has occurred "], 500);
            }
    }
}
