<?php

namespace App\Http\Controllers;

use App\Models\UserFavoriteData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use App\Libraries;

class UserFavoriteDataController extends Controller
{
    //
    public function addUserFavoriteData(Request $request)
    {
        // decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        // get input params
        $studyAmount=$request->studyAmount;
        $bookType=$request->bookType;
        $howToBuy=$request->howToBuy;
        $importantThing=$request->importantThing;
        $userAgeRange=$request->userAgeRange;
        $favoriteCategory=$request->favoriteCategory;

        try {
            $userFavoriteData=UserFavoriteData::where('userId',$identifiedUser->id);
            if (!$userFavoriteData->exists()){
                $userFavoriteDataList=new UserFavoriteData();

                $userFavoriteDataList->userId=$identifiedUser->id;
                $userFavoriteDataList->studyAmount=$studyAmount;
                $userFavoriteDataList->bookType=$bookType;
                $userFavoriteDataList->howToBuy=$howToBuy;
                $userFavoriteDataList->importantThing=$importantThing;
                $userFavoriteDataList->userAgeRange=$userAgeRange;
                $userFavoriteDataList->favoriteCategory=$favoriteCategory;

                $userFavoriteDataList->save();
            }else{
                //update user favorite data
                $updateList=[
                    'studyAmount'=> $studyAmount,
                    'bookType'=> $bookType,
                    'howToBuy'=> $howToBuy,
                    'importantThing'=> $importantThing,
                    'userAgeRange'=> $userAgeRange,
                    'favoriteCategory'=>$favoriteCategory
                ];

                $userFavoriteData->update($updateList);
            }
            return response()->json(['message'=>'update user favorite data successfully'],200);
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function getUserFavoriteData(Request $request)
    {
        // decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        try {
            $userFavoriteData=UserFavoriteData::where('userId',$identifiedUser->id);
            if ($userFavoriteData->exists()){
                $userFavoriteData=$userFavoriteData->first();
                return response()->json(['data'=>$userFavoriteData,'message'=>'return user favorite data successfully'],200);
            }else{
                return response()->json(['status'=>'error','message'=>'book not found in this user\'s favorite list'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }
//    public function editUserFavoriteData(Request $request)
//    {
//        // decode bearer token
//        $helper=new Libraries\Helper();
//        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());
//
//        // get input params
//        $studyAmount=$request->studyAmount;
//        $bookType=$request->bookType;
//        $howToBuy=$request->howToBuy;
//        $importantThing=$request->importantThing;
//        $userAgeRange=$request->userAgeRange;
//        $favoriteCategory=$request->favoriteCategory;
//
//
//        try {
//            $userFavoriteData=UserFavoriteData::where('userId',$identifiedUser->id);
//            if ($userFavoriteData->exists()){
//                $updateList=[
//                    'studyAmount'=> $studyAmount,
//                     'bookType'=> $bookType,
//                     'howToBuy'=> $howToBuy,
//                     'importantThing'=> $importantThing,
//                     'userAgeRange'=> $userAgeRange,
//                     'favoriteCategory'=>$favoriteCategory
//                ];
//
//                $userFavoriteData->update($updateList);
//                return response()->json(['message'=>'edit user favorite data successfully'],200);
//            }else{
//                return response()->json(['status'=>'error','message'=>'favorite data has not been previously registered for this user to edit'],404);
//            }
//        }catch (\Exception $e){
//            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
//        }
//    }
}
