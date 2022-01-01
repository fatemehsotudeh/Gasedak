<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Store;
use App\Models\StoreAddress;
use App\Models\User;
use Illuminate\Http\Request;

use App\Libraries;

class SearchController extends Controller
{
    //
    public function searchByLocation(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $keyWord=$request->keyWord;
        $lat=$request->lat;
        $lng=$request->lng;

        //Check that lat and lng are not empty
        if (empty($lat) or empty($lng)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill the fields']);
        }

        try {
              $listLat2=StoreAddress::all()->pluck('lat','id');
              $listLng2=StoreAddress::all()->pluck('lng','id');

              $distances=[];
              foreach ($listLng2 as $key=>$value){
                  $distance=$helper->distance(floatval($lat),floatval($lng),floatval($listLat2[$key]),floatval($listLng2[$key]),'k');
                  $distances[$key]=$distance;
              }
              asort($distances);

              foreach ($distances as $key=>$value){
                  $data[]=StoreAddress::where('storesaddress.id',$key)
                      ->join('stores','stores.id','storesaddress.storeId')
                      ->get()[0];
              }
              return response()->json(['data' => $data, 'message' => 'return stores successfully'], 500);
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
