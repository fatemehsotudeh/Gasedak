<?php

namespace App\Http\Controllers;

use App\Models\Book;
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
        //Get input information
        $bookId=$request->bookId;
        $userLat=$request->lat;
        $userLng=$request->lng;

        //Check that the bookId field is not empty
        if (empty($bookId) ||  empty($userLat) || empty($userLng)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the fields']);
        }

        try {
            $book=Book::where('id',$bookId);
            if ($book->exists()){
                //get book data
                $data=$book->first();

                //find stores that have this book
                $bookName=$data['name'];
                $storesLatAndLng=$this->findStoresWithThisBook($bookName);
                $distancesAndIds=$this->getUserDistanceToBookStores($userLat,$userLng,$storesLatAndLng);
                $stores=$this->getStoreDataBasedNearest($distancesAndIds);

                $data['stores']=$this->paginateStores($request,$stores);

                //get book comments

                //Increase the number of views of this book
                $viewCount=$book->pluck('viewCount')[0];
                $viewCount++;
                $book->update(['viewCount'=>$viewCount]);

                return response()->json(['data'=>$data,'message'=>'return books data successfully'],200);
            }else{
                return response()->json(['status'=>'error','message'=>'no books found with this id'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }

    }

    public function findStoresWithThisBook($bookName)
    {
        $booksId=Book::where([['name',$bookName]])->pluck('id');

        $storeIds=[];
        foreach ($booksId as $bookId){
            $ids=storeBook::where('bookId',$bookId)->pluck('storeId');
            foreach ($ids as $storeId){
                array_push($storeIds,$storeId);
            }
        }
        return $this->getStoresLatAndLng($storeIds);
    }

    public function getStoresLatAndLng($storeIds)
    {
        $lats=[];
        $lngs=[];

        foreach ($storeIds as $storeId){
            $lat=StoreAddress::where('storeId',$storeId)->pluck('lat')[0];
            $lats[$storeId]=$lat;
            $lng=StoreAddress::where('storeId',$storeId)->pluck('lng')[0];
            $lngs[$storeId]=$lng;
        }
        return ['lats'=>$lats,'lngs'=>$lngs];
    }

    public function getUserDistanceToBookStores($userLat,$userLng,$storesLatAndLng)
    {
        $helper=new Libraries\Helper();

        $storesLat=$storesLatAndLng['lats'];
        $storesLng=$storesLatAndLng['lngs'];

        $distances=[];

        foreach ($storesLng as $key=>$value){
            $distance=$helper->distance(floatval($userLat),floatval($userLng),floatval($storesLat[$key]),floatval($storesLng[$key]),'k');
            $distances[$key]=$distance;
        }

        asort($distances);

        return $distances;
    }

    public function getStoreDataBasedNearest($distances)
    {
        $stores=[];
        foreach ($distances as $storeId=>$distance){
            $stores[]=Store::where('stores.id',$storeId)
                ->join('storesaddress','storesaddress.storeId','stores.id')
                ->first();
        }
        return $stores;
    }

    public function paginateStores($request,$stores)
    {
        $helper=new Libraries\Helper();

        $collectionData=collect($stores);
        return $helper->paginate($request,$collectionData);
    }
}
