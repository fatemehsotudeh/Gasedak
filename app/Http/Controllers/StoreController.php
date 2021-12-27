<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    //
    public function getStoreData(Request $request)
    {
        $storeId=$request->id;
        try{
            $storeData=Store::where('stores.id',$storeId);
            if ($storeData->exists()) {
                $storeData=$storeData->join('storesaddress','storesaddress.storeId','=','stores.id');
                return response()->json(['message'=>'return bookstore data successfully','data'=>$storeData->get()],200);
            }else{
                return response()->json(['status'=>'error','message'=>'no store found with this id'],404);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }
}
