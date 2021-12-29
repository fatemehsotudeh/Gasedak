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

        // Check if field empty
        if (empty($studyAmount) || empty($bookType) || empty($howToBuy) || empty($importantThing)){
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        try {
            $userFavoriteData=UserFavoriteData::where('userId',$identifiedUser->id);
            if (!$userFavoriteData->exists()){
                $userFavoriteDataList=new UserFavoriteData();

                $userFavoriteDataList->userId=$identifiedUser->id;
                $userFavoriteDataList->studyAmount=$studyAmount;
                $userFavoriteDataList->bookType=$bookType;
                $userFavoriteDataList->howToBuy=$howToBuy;
                $userFavoriteDataList->importantThing=$importantThing;

                $userFavoriteDataList->save();
                return response()->json(['message'=>'add user favorite data successfully'],200);
            }else{
                return response()->json(['status'=>'error','message'=>'favorite data has already been added for this user'],422);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function editUserFavoriteData(Request $request)
    {
        // decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        // get input params
        $studyAmount=$request->studyAmount;
        $bookType=$request->bookType;
        $howToBuy=$request->howToBuy;
        $importantThing=$request->importantThing;

        // Check if field empty
        if (empty($studyAmount) || empty($bookType) || empty($howToBuy) || empty($importantThing)){
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        try {
            $userFavoriteData=UserFavoriteData::where('userId',$identifiedUser->id);
            if ($userFavoriteData->exists()){
                $updateList=[
                    'studyAmount'=>$studyAmount,
                     'bookType'=>$bookType,
                     'howToBuy'=>$howToBuy,
                     'importantThing'=>$importantThing
                ];

                $userFavoriteData->update($updateList);
                return response()->json(['message'=>'edit user favorite data successfully'],200);
            }else{
                return response()->json(['status'=>'error','message'=>'favorite data has not been previously registered for this user to edit'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }
}
