<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\RecentSearch;
use App\Models\Store;
use App\Models\StoreAddress;
use Illuminate\Http\Request;

use App\Libraries;

class SearchController extends Controller
{
    //
    public function searchByLocation(Request $request)
    {
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //Get input parameters
        $keyWord=$request->keyWord;
        $userLat=$request->lat;
        $userLng=$request->lng;

        //Check that lat and lng are not empty
        if (empty($userLat) or empty($userLng)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill the fields']);
        }

        try {
            $data=[];
            //If the keyword is empty, the store will list nearest to the user in order
            //If it is not empty, the stores whose names correspond to this keyword will be sorted in the nearest order
            if (empty($keyWord)){
                $distances=$helper->calculateUserDistanceToBookStores($userLat,$userLng);

                //Specify the distance from the user to the existing bookstores
                //Show the list of bookstores based on the nearest to the user
                foreach ($distances as $id=>$distance){
                    $data[]=StoreAddress::where('storesaddress.id',$id)
                        ->join('stores','stores.id','storesaddress.storeId')
                        ->get()[0];
                }

            }else{
                //save keyWord in recent search table
                $this->saveKeyWord($keyWord,$identifiedUser->id);

                $distances=$helper->calculateUserDistanceToBookStoresByKeyWord($userLat,$userLng,$keyWord);

                //Specify the distance from the user to the existing bookstores where name stores like keyWord
                //Show the list of bookstores based on the nearest to the user
                foreach ($distances as $id => $distance){
                    $data[]=StoreAddress::where('storesaddress.id',$id)
                        ->join('stores','stores.id','storesaddress.storeId')
                        ->get()[0];
                }
            }

            //If the array is empty,show the message that the bookstore could not be found
            if (sizeof($data)==0){
                return response()->json(['status' => 'error', 'message' => 'No such bookstore was found'], 404);
            }

            return response()->json(['data' => $data, 'message' => 'return stores successfully'], 200);
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function saveKeyWord($keyWord,$userId)
    {
        //If the keywords for this user are less than 5, this keyword will be added. Otherwise,
        //first a row of the table that has a later date than the others will be deleted and then the new word saveed
        $userKeyWord=RecentSearch::where('userId',$userId);

        $recentSearch=new RecentSearch();
        $recentSearch->userId=$userId;
        $recentSearch->keyWord=$keyWord;

        if ($userKeyWord->exists()){
            if (sizeof($userKeyWord->get())>=5){
                $recentSearch->where('userId',$userId)->first()->delete();
            }
        }
        $recentSearch->save();
    }

    public function frequentSearches(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        try {
            $userRecentSearches=RecentSearch::where('userId',$identifiedUser->id);
            if ($userRecentSearches->exists()){
                $userRecentSearches=$userRecentSearches->orderBy('created_at','DESC')->get();
                return response()->json(['data' => $userRecentSearches, 'message' => 'return user recent searches successfully'], 200);
            }else{
                return response()->json(['status' =>'error', 'message' => 'the keyword that this user recently searched for was not found'], 404);
            }
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
