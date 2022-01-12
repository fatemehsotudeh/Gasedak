<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Store;
use App\Models\StoreBook;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    //
    public function getStoreData(Request $request)
    {
        $storeId=$request->storeId;

        //Check that the storeId field is not empty
        if (empty($storeId)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the storeId field']);
        }

        try{
            $store=Store::where('stores.id',$storeId);

            if ($store->exists()) {
                //When this api is called, the user has visited that store,
                //so a unit is added to the number of views
                $viewCount=$store->pluck('viewCount')[0];
                $viewCount++;
                $store->update(['viewCount'=>$viewCount]);

                $storeData=$store->join('storesaddress','storesaddress.storeId','=','stores.id');

                return response()->json(['message'=>'return bookstore data successfully','data'=>$storeData->first()],200);
            }else{
                return response()->json(['status'=>'error','message'=>'no store found with this id'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function getStoreBooks(Request $request)
    {
        $publisherStoreId=$request->id;
        try{
            $publisherStoreData=StoreBook::where('storebooks.storeId',$publisherStoreId);
            if ($publisherStoreData->exists()) {
                $storeBooks=$publisherStoreData->join('books','books.id','=','storebooks.bookId');
                $storeBooks=$storeBooks->paginate(10);
                return response()->json(['message'=>'return store books successfully','data'=>$storeBooks],200);
            }else{
                return response()->json(['status'=>'error','message'=>'no books were found for this store'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }
}
