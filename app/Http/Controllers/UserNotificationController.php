<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\UserNotification;
use Illuminate\Http\Request;

use App\Libraries;

class UserNotificationController extends Controller
{
    //
    public function getUserNotifications(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //If notification is found for this user,
        //it is returned and the isSeen field becomes one,
        //otherwise an error
        try{
            $userNotifications=UserNotification::where('userId',$identifiedUser->id);
            if ($userNotifications->exists()) {
                $userNotifications->update(['isSeen'=>1]);
                $userNotifications=$userNotifications->join('notifications', 'notifications.id', '=', 'notificationId');
                return response()->json(['message'=>'return user notifications successfully','data'=>$userNotifications->get()],200);
            }else{
                return response()->json(['status'=>'error','message'=>'no notifications found for this user'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }
}
