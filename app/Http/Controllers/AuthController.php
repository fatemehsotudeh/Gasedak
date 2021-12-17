<?php

namespace App\Http\Controllers;

use App\Models\InvitationalCode;
use App\Models\SMSToken;
use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\Providers\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\JWTGuard;

use App\Libraries;


class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $phoneNumber = $request->phoneNumber;
        $password = $request->password;
        $invitationalCode=$request->invitationalCode;

        // Check if field is empty
        if (empty($phoneNumber) or empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        //check phoneNumber
        if(!preg_match("/^[0-9]{11}$/", $phoneNumber)) {
            return response()->json(['status' => 'error', 'message' => 'You must provide the correct phoneNumber']);
        }

        // Check if password is less than 6 character
        if (strlen($password) < 6) {
            return response()->json(['status' => 'error', 'message' => 'Password should be min 6 character']);
        }

        // Check if user already exist
        if (User::where('phoneNumber', '=', $phoneNumber)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'User already exists with this phoneNumber'],422);
        }

        // Create new user if verified

        if (SMSToken::where(['phoneNumber'=>$phoneNumber,'isVerified'=>1])->exists()) {
            try {
                $user = new User();
                $user->phoneNumber = $phoneNumber;
                $user->password = app('hash')->make($password);

                if ($user->save()) {
                    //create invitationalCode for user
                    $invitCode=new InvitationalCode();

                    //generate 8 digits random invitational code for user
                    $helper=new Libraries\Helper();
                    $randCode=$helper->generateAlphaNumericCode(8);

                    //Check that the random code is unique
                    while (InvitationalCode::where('invitationalCode','randCode')->exists()){
                        $randCode=$helper->generateAlphaNumericCode(8);;
                    }

                    //save generated invitational code in invitationalCodes table
                    $invitCode->invitationalCode=$randCode;
                    $invitCode->userId=$user->id;
                    $invitCode->save();

                    $invitCode=new InvitationalCode();

                    //Set user information if entered the invitation code
                    if (!empty($invitationalCode)&&($foundCodeRow=InvitationalCode::where([['userId','!=',$user->id],['invitationalCode', $invitationalCode]])->exists())){
                        $phone=User::where('id',$user->id)->pluck('phoneNumber');
                        $phones=InvitationalCode::where([['userId','!=',$user->id],['invitationalcode',$invitationalCode]])->pluck('usedBy')[0];
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
                        }

                    $invitCode->usedBy=$phones;

                    InvitationalCode::where('invitationalCode',$invitationalCode)->update(['usedBy' => $invitCode->usedBy,'invitationUsed'=>1]);

                    }
                    return $this->login($request);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
        }else{
            return response()->json(['status' => 'error', 'message' => 'User not identified'],401);
        }

    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function login(Request $request)
    {
        $phoneNumber = $request->phoneNumber;
        $password = $request->password;

        // Check if field is empty
        if (empty($phoneNumber) or empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // Check if password is less than 6 character
        if (strlen($password) < 6) {
            return response()->json(['status' => 'error', 'message' => 'Password should be min 6 character']);
        }

        //check phoneNumber
        if(!preg_match("/^[0-9]{11}$/", $phoneNumber)) {
            return response()->json(['status' => 'error', 'message' => 'You must provide the correct phoneNumber']);
        }

        $credentials = request(['phoneNumber', 'password']);

        if (!$token =auth()->setTTL(1440)->attempt($credentials)){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token,$request);
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, Request $request)
    {
        return response()->json([
            'data'=>[
                'phoneNumber'=>$request->phoneNumber,
                'password'=>$request->password,
            ],
            'access_token' => $token,
//            'token_type' => 'bearer',
//            'expires_in' => auth()->factory()->getTTL() *60
        ],200);
    }

    public function resetPassword(Request $request)
    {
        $phoneNumber = $request->phoneNumber;
        $code=$request->code;
        $newPassword = $request->newPassword;

        // Check if field is empty
        if (empty($phoneNumber) or empty($code) or empty($newPassword)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }
        // Check if password is less than 6 character
        if (strlen($newPassword) < 6) {
            return response()->json(['status' => 'error', 'message' => 'Password should be min 6 character']);
        }
        //check phoneNumber
        if(!preg_match("/^[0-9]{11}$/", $phoneNumber)) {
            return response()->json(['status' => 'error', 'message' => 'You must provide the correct phoneNumber']);
        }


        $userData=SMSToken::where('phoneNumber',$phoneNumber);
        try {
            if ($userData->exists()) {
                $user = $userData->get();
                $user = json_decode($user[0], false);

                //diff between two datetime to check if smsCode expired or not
                $helper=new Libraries\Helper();
                $diff=$helper->diffDate(date('Y-m-d H:i:s'),$user->updated_at);

                if ($diff <= 120 && $user->smsCode == $code) {
                    $hashPassword=app('hash')->make($newPassword);
                    if(User::where('phoneNumber',$phoneNumber)->update(['password'=>$hashPassword])){
                        return response()->json(["message" => "reset password successfully"], 200);
                    }
                } else if ($diff > 120 && $user->smsCode == $code) {
                    $userData->update(['smsCode' => 'null']);
                    return response()->json(["message" => "Code expired"], 403);
                } else if ($user->smsCode != $code) {
                    return response()->json(["message" => "Code incorrect"], 401);
                }
            }
        }catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
};
