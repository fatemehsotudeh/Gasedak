<?php

namespace App\Http\Controllers;

use App\Models\CartHelper;
use App\Models\StoreBook;
use App\Models\UserFavoriteGood;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use App\Libraries;

class BookController extends Controller
{
    //
    public function getBookData(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //Get input information
        $bookId=$request->bookId;

        //Check that the bookId field is not empty
        if (empty($bookId)){
            return response()->json(['status' => 'error', 'message' => 'you must fill the bookId field']);
        }

        $storeBook=new StoreBook();
        $storeBook->bookId=$bookId;


        if (!$storeBook->checkExistenceBookInStore()){
            return response()->json(['status' => 'error', 'message' => 'no books were found with this id'],404);
        }

        try {
            $storeBook->updateBookCountInfo();
            $data=$storeBook->getBookData();
            $data['image']=$storeBook->getBookImage();
            $data['hashtags']=$storeBook->getBookHashtagsAndConvertToArray();
            $userFavGood=new UserFavoriteGood();
            $userFavGood->userId=$identifiedUser->id;
            $userFavGood->bookId=$bookId;
            $data['isBookInFavList']=$userFavGood->isbookInFavList();
            $cart=new CartHelper();
            $cart->userId=$identifiedUser->id;
            $cart->bookId=$bookId;
            $data['isBookInCart']=$cart->isBookInCart();
            return response()->json(['data'=>$data,'message'=>'return book data successfully'],200);

        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function getStoresWithThisBook(Request $request)
    {
        //Get input information
        $bookId=$request->bookId;
        $userLat=$request->lat;
        $userLng=$request->lng;

        //Check that the bookId field is not empty
        if (empty($bookId) ||  empty($userLat) || empty($userLng)){
            return response()->json(['status' => 'error', 'message' => 'you must fill the fields']);
        }

        $storeBook=new StoreBook();
        $storeBook->bookId=$bookId;

        if (!$storeBook->checkExistenceBookInStore()){
            return response()->json(['status' => 'error', 'message' => 'no books were found with this id'],404);
        }

        try {
            $storesId=$storeBook->getStoresIdWithThisBook();
            $storesLatAndLng=$storeBook->getStoresLatAndLng($storesId);
            $distancesAndIds=$storeBook->getUserDistanceToBookStores($userLat,$userLng,$storesLatAndLng);
            $stores=$storeBook->getSpecificStoreDataBasedNearest($distancesAndIds,$bookId);
            $storesCheckDiscountAndNotSuspended=$storeBook->checkDiscountsAndSuspended($stores);
            $data=$storeBook->paginateData($request,$storesCheckDiscountAndNotSuspended);

            return response()->json(['data'=>$data,'message'=>'return stores with this book successfully'],200);

        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function getBookComments(Request $request)
    {
        $bookId=$request->bookId;

        //Check that the bookId field is not empty
        if (empty($bookId)){
            return response()->json(['status' => 'error', 'message' => 'you must fill the bookId field']);
        }

        $storeBook=new StoreBook();
        $storeBook->bookId=$bookId;

        if (!$storeBook->checkExistenceBookInStore()){
            return response()->json(['status' => 'error', 'message' => 'no books were found with this id'],404);
        }

        try {
            $data=$storeBook->getBookComments($request);
            if ($data==[]){
                return response()->json(['status'=>'error' ,'message'=>'no comments found for this book'],404);
            }else{
                return response()->json(['data'=>$data,'message'=>'return book comments successfully'],200);
            }

        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

}
