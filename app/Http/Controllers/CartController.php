<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use App\Models\CartHelper;
use App\Models\CartItem;
use Illuminate\Http\Request;

use App\Libraries;

class CartController extends Controller
{
    //
    public function addToCart(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //get inputs
        $storeId=$request->storeId;
        $bookId=$request->bookId;

        //Check that the inputs are not empty
        if (empty($storeId) || empty($bookId)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the fields']);
        }

        //create object from cartHelper model
        $cartHelper=new CartHelper();
        $cartHelper->userId=$identifiedUser->id;
        $cartHelper->storeId=$storeId;
        $cartHelper->bookId=$bookId;

        //Check the existence of the store with this id
        $store=$cartHelper->checkExistenceStore();
        if (!$store){
            return response()->json(['status'=>'error','message'=>'no store was found with this id'],404);
        }

        //Check the existence of the book with this id
        $book=$cartHelper->checkExistenceBook();
        if (!$book){
            return response()->json(['status'=>'error','message'=>'no book was found with this id'],404);
        }

        //Check the availability of this book in the store
        $bookStore=$cartHelper->checkExistenceBookStore();
        if (!$bookStore){
            return response()->json(['status'=>'error','message'=>'no book with this id was found in this store'],404);
        }

        //Check if a cart has already been created for this store or not
        //If the shopping cart was created correctly for this store before, it will be added to the products of the same store
        //Otherwise a new cart will be created for the store
        try{
            $checkInventory=$cartHelper->checkInventory();
            if ($checkInventory){
                $resultStoreInCart=$cartHelper->checkStoreInCart();
                return $cartHelper->createOrUpdateCart($resultStoreInCart);
            }else{
                return response()->json(['status'=>'error','message'=>'The stock of this book is zero and it is not possible to add it to the cart'],400);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

//    public function getBookPrice($bookId)
//    {
//        return Book::where('id',$bookId)->pluck('price');
//    }

//    public function getBookDiscount($bookId)
//    {
//        //check Daily discount
//        $discountAmount=$this->getDailyDiscount($bookId);
//        if ($discountAmount==0){
//            //get normal discount
//            $discountAmount=$this->getNormalDiscount($bookId);
//        }
//        return $discountAmount;
//    }

//    public function getDailyDiscount($bookId)
//    {
//        $book=Book::where('id',$bookId);
//        $dailyDiscount=$book->pluck('dailyDiscount');
//        $dailyDiscountExpDate=$book->pluck('dailyDiscountExpDate');
//
//        if ($dailyDiscount!=0){
//            //check exp date
//            if ($this->checkDailyDiscountExpDate($bookId,$dailyDiscountExpDate)){
//                return $dailyDiscount;
//            }else{
//                return 0;
//            }
//        }else{
//            return 0;
//        }
//    }

//    public function checkDailyDiscountExpDate($bookId,$expDate)
//    {
//        $currentDate=$this->getCurrentTimeStamp();
//        if ($expDate<$currentDate){
//            return true;
//        }else{
//            return false;
//        }
//    }

//    public function getCurrentTimeStamp()
//    {
//        date_default_timezone_set('Asia/Tehran');
//        return date('Y-m-d H:i:s');
//    }
//
//    public function getNormalDiscount($bookId)
//    {
//        return Book::where('id',$bookId)
//            ->pluck('discountAmount');
//    }

}
