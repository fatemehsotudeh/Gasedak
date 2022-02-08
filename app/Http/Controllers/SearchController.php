<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\RecentSearch;
use App\Models\Store;
use App\Models\StoreAddress;
use App\Models\StoreBook;

use Illuminate\Database\Eloquent\Model;
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

        $storeBook=new StoreBook();

        //Check that lat and lng are not empty
        if (empty($userLat) or empty($userLng)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill the fields']);
        }

        try {
            //If the keyword is empty, the store will list nearest to the user in order
            //If it is not empty, the stores whose names correspond to this keyword will be sorted in the nearest order
            if (empty($keyWord)){
                $storesLatAndLng=$storeBook->getStoresLatAndLng();
                $distancesAndIds=$storeBook->getUserDistanceToBookStores($userLat,$userLng,$storesLatAndLng);
                $data=$storeBook->getAllStoreDataBasedNearest($request,$distancesAndIds);
            }else{
                $this->saveKeyWord($keyWord,$identifiedUser->id);
                $storesLatAndLng=$storeBook->getStoresLatAndLng(null,$keyWord);
                $distancesAndIds=$storeBook->getUserDistanceToBookStores($userLat,$userLng,$storesLatAndLng);
                $data=$storeBook->getAllStoreDataBasedNearest($request,$distancesAndIds);
            }

            if ($data==[]){
                return response()->json(['status' => 'error', 'message' => 'stores not found'], 404);
            }

            return response()->json(['data' => $data, 'message' => 'stores were successfully returned based on the nearest to the user'], 200);

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
            $storeBooks=new StoreBook();
            $storeBooks->storeId=$storeId;
            if ($storeBooks->checkStoreHasBooks()){
                    if ($storeBooks->checkStoreNotSuspended()){
                        $allThisStoreBooks=$storeBooks->getStoreAllBooks();
                        $booksFoundWithKeyword=$storeBooks->advanceSearchInBooks($allThisStoreBooks,$keyWord);
                        $booksFoundWithImageAndUpdatedDiscounts=$storeBooks->checkBookDiscountsAndAddImage($booksFoundWithKeyword);
                        if ($booksFoundWithImageAndUpdatedDiscounts!=[]){
                            $data=$storeBooks->paginateData($request,$booksFoundWithImageAndUpdatedDiscounts);
                            return response()->json(['data' => $data, 'message' => 'return books successfully'],200);
                        }else{
                            return response()->json(['status' => 'error', 'message' => 'no books were found for this store with this keyword'],404);
                        }
                    }else{
                        return response()->json(['status' => 'error', 'message' => 'this store is suspended'],400);
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
            $storeBook=new StoreBook();
            $categories=$storeBook->getAllCategories();
            if ($storeBook->checkExistenceCategory($categories,$category)){
                $categoryId=$storeBook->categoryId;
                $booksRelatedCategory=$storeBook->getBooksRelatedCategory();
                $booksWithPriceAndImage=$storeBook->addMinimumPriceAndDiscountToBooksFromStores($booksRelatedCategory);
                $booksRelatedCategoryOrderBy=$storeBook->getOrderByResults($booksWithPriceAndImage,$orderBy);
                if (empty($keyWord)){
                    if (sizeof($booksRelatedCategoryOrderBy)!=0){
                        $data=$storeBook->paginateData($request,$booksRelatedCategoryOrderBy);
                        return response()->json(['data' =>$booksRelatedCategoryOrderBy, 'message' => 'books related to this category were returned'], 200);
                    }else{
                        return response()->json(['status' =>'error', 'message' => 'no books were found for this category'], 404);
                    }
                }else{
                    //save keyWord in recent search table
                   $this->saveKeyWord($keyWord,$identifiedUser->id);
                   $foundBooksRelatedKeyword=$storeBook->advanceSearchInBooks($booksRelatedCategoryOrderBy,$keyWord);
                   if (sizeof($foundBooksRelatedKeyword)!=0){
                       $data=$storeBook->paginateData($request,$foundBooksRelatedKeyword);
                       return response()->json(['data' =>$data, 'message' => 'books related to this category and keyword were returned'], 200);
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
            $storeBook=new StoreBook();
            $book=Book::where('hashtags','like','%'.$hashtag.'%')->get();
            $booksWithPriceAndImage=$storeBook->addMinimumPriceAndDiscountToBooksFromStores($book);
            $stores=Store::where([['hashtags','like','%'.$hashtag.'%'],['isSuspended','!=',1]]);

            if (sizeof($booksWithPriceAndImage)!=0){
                $books=$booksWithPriceAndImage->values()->toArray();
                foreach ($books as $key=>$book){
                    $books[$key]['type']='book';
                }
            }else{
                $books=[];
            }

            if ($stores->exists()){
                $stores=$stores->get()->toArray();
                foreach ($stores as $key=>$store){
                    $stores[$key]['type']='store';
                }
            }else{
                $stores=[];
            }

            $data=array_merge($books,$stores);

            if (sizeof($data)>0){
                $paginateData=$storeBook->paginateData($request,$data);
                return response()->json(['data' => $paginateData, 'message' => 'books or stores that had this hashtag were successfully returned'], 200);
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

        try {
            //save keyWord in recent search table
            $this->saveKeyWord($keyWord,$identifiedUser->id);
            $storeBook=new StoreBook();
            $allBooks=$storeBook->getAllBooks();
            $allStores=$storeBook->getAllStores();
            $foundBooksWithKeyword=$storeBook->advanceSearchInBooks($allBooks,$keyWord);
            $booksWithPriceAndImage=$storeBook->addMinimumPriceAndDiscountToBooksFromStores($foundBooksWithKeyword);
            $foundStoresWithThisKeyword=$storeBook->advanceSearchInStores($allStores,$keyWord);
            $foundResults=[];
            $foundResults['books']=$booksWithPriceAndImage;

            $foundResults['stores']=$foundStoresWithThisKeyword;

            if (sizeof($foundResults['books'])!=0){
                $foundResults['books']=$storeBook->paginateData($request,$foundResults['books']);
            }

            if (sizeof($foundResults['stores'])!=0){
                $foundResults['stores']=$storeBook->paginateData($request,$foundResults['stores']);
            }

            if (sizeof($foundResults['stores'])>0 || sizeof($foundResults['books'])>0){
                return response()->json(['data' => $foundResults, 'message' => 'list of books or bookstores that have this keyword was successfully returned'], 200);
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

}
