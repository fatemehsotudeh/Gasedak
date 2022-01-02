<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Store;
use App\Models\storeBook;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    //
    public function getStoreData(Request $request)
    {
        $storeId=$request->id;
        try{
            $store=Store::where('stores.id',$storeId);

            if ($store->exists()) {
                //When this api is called, the user has visited that store,
                //so a unit is added to the number of views
                $viewCount=$store->pluck('viewCount')[0];
                $viewCount++;
                $store->update(['viewCount'=>$viewCount]);

                $storeData=$store->join('storesaddress','storesaddress.storeId','=','stores.id');

                return response()->json(['message'=>'return bookstore data successfully','data'=>$storeData->get()],200);
            }else{
                return response()->json(['status'=>'error','message'=>'no store found with this id'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function getStoreBooks(Request $request)
    {
        $storeId=$request->id;
        try{
            $storeData=storeBook::where('storebooks.storeId',$storeId);
            if ($storeData->exists()) {
                $storeBooks=$storeData->join('books','books.id','=','storebooks.bookId');
                return response()->json(['message'=>'return store books successfully','data'=>$storeBooks->get()],200);
            }else{
                return response()->json(['status'=>'error','message'=>'no books were found for this store'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }
}
