<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAvatar;
use Illuminate\Http\Request;

use App\Libraries;
use function PHPUnit\Framework\isNull;

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

        // Check if fields empty
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

    public function updateProfile(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $firstname=$request->firstname;
        $lastname=$request->lastname;
        $email=$request->email;
        $gender=$request->gender;
        $birthdate=$request->birthdate;

//        //validate email
//        if (!$helper->isValidEmail($email)) {
//            return response()->json(['message' => 'email is not a valid email address']);
//        }
//
//        //check firstname and lastname length
//        if (strlen($firstname)>=55 or strlen($lastname)>=55) {
//            return response()->json(['message' => 'name is not valid']);
//        }

        try {
            $updatedFields=[
                'firstname'=> $firstname,
                'lastname'=> $lastname,
                'email'=> $email,
                'gender'=>$gender,
                'birthdate'=>$birthdate
            ];
            $user=User::where('id',$identifiedUser->id);
            $userProfileData=$user->get()[0];
            if($user->update($updatedFields)){
                return response()->json(['data'=>$userProfileData,'message'=>'update profile info successfully'],200);
            }

        }catch (\Exception $e){
            return response()->json(['massage'=>$e->getMessage()],500);
        }
    }

    public function getUserInfo(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        try {
            $user=User::where('id',$identifiedUser->id);
            if ($user->exists()){
                $userProfileData=$user->get()[0];
//              dd(UserAvatar::where('userId',$identifiedUser->id)->pluck("imagePath"));
                if(!empty($image=UserAvatar::where('userId',$identifiedUser->id)->pluck('imagePath')->first())){
                    $userProfileData->imagePath=$image;
                }else{
                    $userProfileData->imagePath="";
                }
                return response()->json(['data'=>$userProfileData,'message'=>'return user info successfully'],200);
            }else{
                return response()->json(['status'=>'error','message'=>'no information has been registered for this user'],404);
            }

        }catch (\Exception $e){
            return response()->json(['massage'=>$e->getMessage()],500);
        }
    }

    public function checkUserDisabled(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        try {
            $user=User::where('id',$identifiedUser->id);
            if ($user->exists()){
                if ($user->pluck('disabled')[0]==1){
                    return response()->json(['status'=>'error','message'=>'this user is disabled'],400);
                }else{
                    return response()->json(['message'=>'user not disabled and can do anything'],200);
                }
            }else{
                return response()->json(['status'=>'error','message'=>'this user not found'],404);
            }
        }catch (\Exception $e){
            return response()->json(['massage'=>$e->getMessage()],500);
        }

    }

}
