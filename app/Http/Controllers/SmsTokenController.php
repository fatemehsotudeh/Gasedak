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
                return response()->json(["message" => "Send smsCode successfully"]);
            }else{
                $smsToken->save();
                return response()->json(["message" => "Send smsCode successfully"]);
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
