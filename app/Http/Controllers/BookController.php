<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Comment;
use App\Models\Store;
use App\Models\StoreAddress;
use App\Models\storeBook;
use Illuminate\Http\Request;

use App\Libraries;

class BookController extends Controller
{
    //
    public function getBookData(Request $request)
    {
        $helper=new Libraries\Helper();

        //Get input information
        $bookId=$request->bookId;
        $userLat=$request->lat;
        $userLng=$request->lng;

        //Check that the bookId field is not empty
        if (empty($bookId) ||  empty($userLat) || empty($userLng)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the fields']);
        }

        $storeBook=new StoreBook();
        $storeBook->bookId=$bookId;

        if (!$storeBook->checkExistenceBookInStore()){
            return response()->json(['status' => 'error', 'message' => 'no books were found with this id'],404);
        }

        try {
            $storeBook->updateBookCountInfo();
            $data=$storeBook->getBookData();
            $storesId=$storeBook->getStoresIdWithThisBook();
            $storesLatAndLng=$storeBook->getStoresLatAndLng($storesId);
            $distancesAndIds=$storeBook->getUserDistanceToBookStores($userLat,$userLng,$storesLatAndLng);
            $stores=$storeBook->getSpecificStoreDataBasedNearest($distancesAndIds);
            $data['image']=$storeBook->getBookImage();
            $data['stores']=$storeBook->paginateData($request,$stores);
            $data['comments']=$storeBook->getBookComments($request);

            return response()->json(['data'=>$data,'message'=>'return book data successfully'],200);

        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

}
