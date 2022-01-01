<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use App\Libraries;

class UserAddressController extends Controller
{
    //
    public function addAddress(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $lat=$request->lat;
        $lng=$request->lng;
        $province=$request->province;
        $city=$request->city;
        $postalCode=$request->postalCode;
        $postalAddress=$request->postalAddress;

        // Check if fields empty
        if (empty($lat) or empty($lng) or empty($province) or empty($city) or empty($postalCode) or empty($postalAddress) ) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // check postalCode length(should be 10 digits)
        if (strlen($postalCode)!=10){
            return response()->json(['status' => 'error', 'message' => 'The postal code must be 10 digits']);
        }

        $userAddress=new UserAddress();
        $userAddress->userId=$identifiedUser->id;
        $userAddress->lat=$lat;
        $userAddress->lng=$lng;
        $userAddress->province=$province;
        $userAddress->city=$city;
        $userAddress->postalCode=$postalCode;
        $userAddress->postalAddress=$postalAddress;

        try {
             $userAddress->save();
             return response()->json(['message' =>'add user address successfully'],200);
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function getAddress(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        try {
            $userAddress=UserAddress::where('userId',$identifiedUser->id);
            if($userAddress->exists()){
                $userAddressList=$userAddress->get();
                return response()->json(['data' =>$userAddressList,'message' =>'get user address successfully'],200);
            }else{
                return response()->json(['status' =>'error','message' =>'the address list is not registered for this user'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function editAddress(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $lat=$request->lat;
        $lng=$request->lng;
        $province=$request->province;
        $city=$request->city;
        $postalCode=$request->postalCode;
        $postalAddress=$request->postalAddress;
        $addressId=$request->addressId;

        // Check if fields empty
        if (empty($lat) or empty($lng) or empty($province) or empty($city) or empty($postalCode) or empty($postalAddress) or empty($addressId)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // check postalCode length(should be 10 digits)
        if (strlen($postalCode)!=10){
            return response()->json(['status' => 'error', 'message' => 'The postal code must be 10 digits']);
        }

        $userAddress=new UserAddress();
        $userAddress->userId=$identifiedUser->id;
        $userAddress->lat=$lat;
        $userAddress->lng=$lng;
        $userAddress->province=$province;
        $userAddress->city=$city;
        $userAddress->postalCode=$postalCode;
        $userAddress->postalAddress=$postalAddress;

        try {
            $userAddressList=UserAddress::where([['userId',$identifiedUser->id],['id',$addressId]]);
            if($userAddressList->exists()){
                $userAddressList->update([
                    'lat' =>$userAddress->lat,
                    'lng' =>$userAddress->lng,
                    'province' =>$userAddress->province,
                    'city' =>$userAddress->city,
                    'postalCode' =>$userAddress->postalCode,
                    'postalAddress' =>$userAddress->postalAddress
                    ]);
                return response()->json(['message' =>'edit user address successfully'],200);
            }else{
                return response()->json(['status' =>'error','message' =>'The address list has not been registered for this user to change'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function deleteAddress(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $addressId=$request->addressId;

        if (empty($addressId)){
            return response()->json(['status' => 'error', 'message' => 'You must fill address id filed']);
        }
        try {
            $userAddress=UserAddress::where([['userId',$identifiedUser->id],['id',$addressId]]);
            if($userAddress->exists()){
                $userAddressList=$userAddress->delete();
                return response()->json(['message' =>'delete user address successfully'],200);
            }else{
                return response()->json(['status' =>'error','message' =>'the address list is not registered for this user'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }

    }
}
