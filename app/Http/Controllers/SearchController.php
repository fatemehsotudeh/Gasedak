<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\RecentSearch;
use App\Models\Store;
use App\Models\StoreAddress;
use App\Models\UserFavoriteData;
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

    public function searchCategory(Request $request)
    {
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $category=$request->category;

        //Check that the keyword field is not empty
        if (empty($category)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the category field']);
        }

        $this->saveKeyWord($category,$identifiedUser->id);

        try {
           $category=Category::where('title','like','%'.$category.'%');
           if ($category->exists()){
               $categoryId=$category->pluck('id')[0];
               $books=Book::where('categoryId',$categoryId);
               if ($books->exists()){
                   return response()->json(['data' =>$books->get(), 'message' => 'books related to this category were returned'], 200);
               }else{
                   return response()->json(['status' =>'error', 'message' => 'no books were found for this category'], 404);
               }
           }else{
               return response()->json(['status' =>'error', 'message' => 'this category does not exist at all'], 404);
           }
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
        if (!$userKeyWord->where('keyWord',$keyWord)->exists()){
            $recentSearch->save();
        }
    }

    public function searchHashtag(Request $request)
    {
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $hashtag=$request->hashtag;

        //Check that the keyword field is not empty
        if (empty($hashtag)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the hashtag field']);
        }

        $this->saveKeyWord($hashtag,$identifiedUser->id);

        try {
            $book=Book::where('hashtags','like','%'.$hashtag.'%');
            $store=Store::where('hashtags','like','%'.$hashtag.'%');
            $data=[];
            if ($book->exists()){
                $books=$book->get();
                $data['books']=$books;
            }
            if ($store->exists()){
                $stores=$store->get();
                $data['stores']=$stores;
            }
            if (sizeof($data)>0){
                return response()->json(['data' => $data, 'message' => 'books or stores that had this hashtag were successfully returned'], 200);
            }else{
                return response()->json(['status' => 'error', 'message' => 'no book or store with this hashtag was found'], 404);
            }
       }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
       }
    }

    public function search(Request $request)
    {
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $keyWord=$request->keyWord;

        //Check that the keyword field is not empty
        if (empty($keyWord)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the keyWord field']);
        }

        $this->saveKeyWord($keyWord,$identifiedUser->id);

        try {
            $book=Book::where('name','like','%'.$keyWord.'%');
            $store=Store::where('name','like','%'.$keyWord.'%');

            $data=[];

            if ($book->exists()){
                $books=$book->get();
                $data['books']=$books;
            }

            if ($store->exists()){
                $stores=$store->get();
                $data['stores']=$stores;
            }

            if (sizeof($data)>0){
                return response()->json(['data' => $data, 'message' => 'list of books or bookstores that have this keyword in their name was successfully returned'], 200);
            }else{
                return response()->json(['status' => 'error', 'message' => 'could not find book or bookstore with the keyword in its name'], 404);
            }
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
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

    public function homeAll(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        return $this->userExclusiveOffer($identifiedUser->id);

    }

    public function userExclusiveOffer($userId)
    {
        return UserFavoriteData::where('userId',$userId)->get();
    }
}
