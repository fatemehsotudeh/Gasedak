<?php

namespace App\Http\Controllers;

use App\Models\InvitationalCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isJson;

class InvitationalCodeController extends Controller
{
    //
    public function registerInvatition(Request $request)
    {
        $token = $request->bearerToken();
        $identifiedUser=json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))));

        try {
            if (!empty($request->code))
            {
                $invitationalCode=$request->code;
            }
            $invitCode=new InvitationalCode();
            if ($foundCodeRow=InvitationalCode::where([['userId','!=',$identifiedUser->id],['invitationalcode',$invitationalCode]])->exists()){
                $phone=User::where('id',$identifiedUser->id)->pluck('phoneNumber');
                $phones=InvitationalCode::where([['userId','!=',$identifiedUser->id],['invitationalcode',$invitationalCode]])->pluck('usedBy')[0];

                //array for who uses this code
                if($phones==null){
                    $phones=[];
                    array_push($phones,$phone[0]);
                }else{
                    $flag=0;
                    foreach ($phones as $item){
                        if ($item!=$phone[0]){
                            $flag=1;
                            array_push($phones,$phone[0]);
                        }
                    }
                    if ($flag==0){
                        return response()->json(['message'=>'register code already exists']);
                    }
                }
                $invitCode->usedBy=$phones;
                if(InvitationalCode::where('invitationalCode',$invitationalCode)->update(['usedBy' => $invitCode->usedBy,'invitationUsed'=>1])){
                    return response()->json(['message'=>'register code successfully'],200);
                }
           }
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getInvatitionCode(Request $request)
    {
        $token = $request->bearerToken();
        $identifiedUser =json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))));

        try {
            $code=InvitationalCode::where('userId',$identifiedUser->id)->get('invitationalCode')[0];
            return response()->json(['data'=>$code,'message'=>'The operation was successful'],200);
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
