<?php

namespace App\Http\Controllers;

use App\Models\UserAvatar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use App\Libraries;

class UserAvatarController extends Controller
{
    //
    public function uploadAvatar(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //check image is exists
        try {
            if ($request->hasFile('image')){
                $uploadImagePath=$request->file('image');
                $imageSize=$request->file('image')->getSize();
                $imageOriginalName=$request->file('image')->getClientOriginalName();
                $maxSize=$helper->maxImageSize();

                //check size of image
                if ($imageSize<=$maxSize){
                    //check type of file
                    if ($helper->isAllowedImageType($request->file('image')->getMimeType())){
                        $imageSavePath=$helper->imageSavePath($identifiedUser->id,$request->file('image')->getMimeType());

                        if(move_uploaded_file($uploadImagePath,$imageSavePath)){
                            $userAvatar=new UserAvatar();
                            $userAvatar->userId=$identifiedUser->id;
                            $userAvatar->imagePath=$imageSavePath;

                            $user=UserAvatar::where('userId',$identifiedUser->id);
                            if ($user->exists()){
                                $user->update(['imagePath'=>$imageSavePath]);
                            }else{
                                $userAvatar->save();
                            }
                            return response()->json(['data'=>$imageSavePath,'message'=>'uploaded image successfully'],200);
                        }

                    }else{
                        return response()->json(['status'=>'error','message'=>'The uploaded file is not a photo'],400);
                    }
                }else{
                    return response()->json(['status'=>'error','message'=>'Photo size is larger than allowed'],400);
                }
            }else{
                return response()->json(['status'=>'error','message'=>'file does not exists'],400);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }

    }

    public function deleteAvatar(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        try {
            $user=UserAvatar::where('userId',$identifiedUser->id);
            if ($user->exists()){
                $imagePath=$user->pluck('imagePath')[0];
                if ($imagePath!=null){
                    unlink($imagePath);
                    if ($user->update(['imagePath'=> null])){
                        return response()->json(['message' =>'delete image successfully'],200);
                    }
                }else{
                    return response()->json(['status' => 'error','message'=> 'the photo has already been deleted'],400);
                }
            }else{
                return response()->json(['status'=> 'error','message'=> 'photo not uploaded by user to delete'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }
}
