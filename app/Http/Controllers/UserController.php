<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use App\Libraries;

class UserController extends Controller
{
    //
    public function updatePassword(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $oldPassword=$request->oldPassword;
        $newPassword=$request->newPassword;

        //check fileds empty
        // Check if field is empty
        if (empty($oldPassword) or empty($newPassword)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // Check if password is less than 6 character
        if (strlen($oldPassword) < 6 or strlen($newPassword) < 6 ) {
            return response()->json(['status' => 'error', 'message' => 'Password should be min 6 character']);
        }

        $hashedPassword=User::where('id',$identifiedUser->id)->pluck('password')[0];

        //check old password match by hashed password in database
        try {
            if (app('hash')->check($oldPassword, $hashedPassword)) {
                $hashedNewPassword=app('hash')->make($newPassword);

                //update password
                if (User::where('id',$identifiedUser->id)->update(['password'=>$hashedNewPassword])){
                    return response()->json(['message'=>'update password successfully'],200);
                }

            }else{
                return response()->json(['message'=>'old password incorrect'],401);
            }
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
