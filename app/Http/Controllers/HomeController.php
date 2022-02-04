<?php

namespace App\Http\Controllers;

use App\Models\Home;

use Illuminate\Http\Request;

use App\Libraries;

class HomeController extends Controller
{
    //
    public function homeAll(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $data=[];

        try {
            $data['banners']=Home::getBanners();
            $data['userExclusiveOffers']=Home::userOffersWithImage($identifiedUser->id);
            $data['dailyDiscounts']=Home::getBooksWithDailyDiscounts($request);
            $data['newestBooks']=Home::getNewestBooks();
            $data['bestSellingBooks']=Home::getBestSellingBooks();
            $data['gasedakOffers']=Home::getGasedakOffers();
            $data['topStores']=Home::getTopStores();
            $data['mostDiscounts']=Home::getMostDiscounts();
            $data['latestPublications']=Home::getLatestPublications();

            return response()->json(['data' =>$data,'message'=>'return categories and banners successfully'],200);

        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }

    }

    public function homeBook(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $data=[];

        try {
            $data['userExclusiveOffers']=Home::userOffersWithImage($identifiedUser->id);
            $data['newestBooks']=Home::getNewestBooks();
            $data['bestSellingBooks']=Home::getBestSellingBooks();
            $data['mostDiscounts']=Home::getMostDiscounts();
            $data['gasedakOffers']=Home::getGasedakOffers();
            $data['topStores']=Home::getTopStores();
            $data['specialPublicationBooks']=Home::getSpecialPublicationBooks();

            return response()->json(['data' =>$data,'message'=>'return categories and banners successfully'],200);

        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

}
