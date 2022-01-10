<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\RecentSearch;
use App\Models\Store;
use App\Models\StoreAddress;
use App\Models\storeBook;

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
                        ->first()->makeHidden(['email','password','IBAN','username']);
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
                        ->first()->makeHidden(['email','password','IBAN','username']);
                }
            }

            if (sizeof($data)==0){
                return response()->json(['status' => 'error', 'message' => 'stores not found'], 404);
            }

            //paginate: using helper class

            $collectionData=collect($data);
            $paginateItems=$helper->paginate($request,$collectionData);

//            $paginateItems['data']=array_values($paginateItems->toArray()['data']);

            return response()->json(['data' => $paginateItems, 'message' => 'stores were successfully returned based on the nearest to the user'], 200);
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function searchStoreBooks(Request $request)
    {
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //Get input parameters
        $storeId=$request->storeId;
        $keyWord=$request->keyWord;

        //Check that the fields are not empty
        if (empty($storeId) || empty($keyWord)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the fields']);
        }

        //save keyWord in recent search table
        $this->saveKeyWord($keyWord,$identifiedUser->id);

        try {
            $store=storeBook::where('storeId',$storeId);
            if ($store->exists()){
                $isOpen=Store::where('id',$storeId)->pluck('isOpen')->first();
                if ($isOpen==0){
                    return response()->json(['status' => 'error', 'message' => 'this store is closed'],400);
                }else{
                    $storeBooks=$store->join('books','books.id','storebooks.bookId')
                        ->where('books.name','like','%'.$keyWord.'%');

                    if ($storeBooks->exists()){
                        $books=$storeBooks->paginate(10);
                        return response()->json(['data' => $books, 'message' => 'return books successfully'],200);
                    }else{
                        return response()->json(['status' => 'error', 'message' => 'no books were found for this store with this keyword'],404);
                    }
                }
            }else{
                return response()->json(['status' => 'error', 'message' => 'bookstore not found with this id'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function searchCategory(Request $request)
    {
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $category=$request->category;
        $keyWord=$request->keyWord;
        $orderBy=$request->orderBy;

        //Check that the category field is not empty
        if (empty($category) || empty($orderBy)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the fields']);
        }

        try {
           $category=Category::where('title','like','%'.$category.'%');

           if ($category->exists()){
               $categoryId=$category->pluck('id')[0];
               if (empty($keyWord)){
                   $books=Book::where('categoryId',$categoryId);
                   if ($books->exists()){
                       $books=$this->getOrderByResults($books,$orderBy)->paginate(10);
                       return response()->json(['data' =>$books, 'message' => 'books related to this category were returned'], 200);
                   }else{
                       return response()->json(['status' =>'error', 'message' => 'no books were found for this category'], 404);
                   }
               }else{
                   //save keyWord in recent search table
                   $this->saveKeyWord($keyWord,$identifiedUser->id);

                   $books=Book::where([['categoryId',$categoryId],['name','like','%'.$keyWord.'%']]);
                   if ($books->exists()){
                       $books=$this->getOrderByResults($books,$orderBy)->paginate(10);
                       return response()->json(['data' =>$books, 'message' => 'books related to this category and keyword were returned'], 200);
                   }else{
                       return response()->json(['status' =>'error', 'message' => 'no books were found for this category or keyWord'], 404);
                   }
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

        //save hashtag in recent search table
        $this->saveKeyWord($hashtag,$identifiedUser->id);

        try {
            $book=Book::where('hashtags','like','%'.$hashtag.'%');
            $store=Store::where('hashtags','like','%'.$hashtag.'%');
            $data=[];
            if ($book->exists()){
                $books=$book->paginate(10);
                $data['books']=$books;
            }
            if ($store->exists()){
                $stores=$store->paginate(10);
                $data['stores']=$stores;
            }
            if (sizeof($data)>0){
                return response()->json(['data' => $data, 'message' => 'books or stores that had this hashtag were successfully returned'], 200);
            }else{
                return response()->json(['status' => 'error', 'message' => 'no books or stores with this hashtag were found'], 404);
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

        //save keyWord in recent search table
        $this->saveKeyWord($keyWord,$identifiedUser->id);

        try {
            $book=Book::where('name','like','%'.$keyWord.'%')
                ->orWhere('originality',$keyWord)
                ->orWhere('publisher','like','%'.$keyWord.'%')
                ->orWhere('authors','like','%'.$keyWord.'%')
                ->orWhere('translators','like','%'.$keyWord.'%')
                ->orWhere('ISBN',$keyWord);


            $store=Store::where('name','like','%'.$keyWord.'%');

            $data=[];

            if ($book->exists()){
                $books=$book->paginate(10);
                $data['books']=$books;
            }

            if ($store->exists()){
                $stores=$store->paginate(10);
                $data['stores']=$stores;
            }

            if (sizeof($data)>0){
                return response()->json(['data' => $data, 'message' => 'list of books or bookstores that have this keyword in their name was successfully returned'], 200);
            }else{
                return response()->json(['status' => 'error', 'message' => 'could not find book or bookstore with the keyword'], 404);
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

    public function getOrderByResults($data,$orderBy)
    {
        switch ($orderBy){
            case 'جدید ترین':
                return $data->orderBy('created_at','DESC');
                break;
            case 'پرفروش ترین':
                return $data->orderBy('purchaseCount','DESC');
                break;
            case 'گران ترین':
                return $data->orderByRaw('(price - discountAmount) DESC');
                break;
            case 'ارزان ترین':
                return $data->orderByRaw('(price - discountAmount)');
                break;
            default:
                return $data;
        }
    }

}
