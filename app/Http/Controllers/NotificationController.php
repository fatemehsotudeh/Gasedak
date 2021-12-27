<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

use App\Libraries;


class NotificationController extends Controller
{
    //
    public function getAllNotifications(Request $request)
    {
        try{
            $allNotifications=Notification::where('isPublic',1);
            if ($allNotifications->exists()) {
                return response()->json(['message'=>'return all notifications successfully','data'=>$allNotifications->get()],200);
            }else{
                return response()->json(['status'=>'error','message'=>'no public notifications found'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

}
