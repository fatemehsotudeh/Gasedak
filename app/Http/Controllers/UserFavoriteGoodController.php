<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\UserFavoriteGood;
use http\Env\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use App\Libraries;

class UserFavoriteGoodController extends Controller
{
    //
    public function addToFavList(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $bookId=$request->bookId;

        if (empty($bookId)){
            return response()->json(['status' => 'error', 'message' => 'The book id field can not be empty']);
        }

        //If there is no book with this ID or
        //if this book has already been added to the user list,
        //an error message will be returned
        //otherwise the book is successfully added to the user favorite list

        try {
            $userFavoriteGood=new UserFavoriteGood();
            $userFavoriteGood->userId=$identifiedUser->id;

            if (Book::where('id',$bookId)->exists()){
                $userFavoriteGood->bookId=$bookId;
                if (UserFavoriteGood::where([['bookId',$bookId],['userId',$identifiedUser->id]])->exists()){
                    return response()->json(['status' => 'error', 'message' => 'this book has already been registered in the list of favorites'], 422);
                }else{
                    $userFavoriteGood->save();
                    return response()->json(['message' => 'the book was successfully added to the users favorite list'], 200);
                }
            }else{
                return response()->json(['status' => 'error', 'message' => 'no books were found with this id'], 404);
            }
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteFromFavList(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $bookId=$request->bookId;

        if (empty($bookId)){
            return response()->json(['status' => 'error', 'message' => 'The book id field can not be empty']);
        }

        try {
            $userFavoriteGood=new UserFavoriteGood();
            $userFavoriteGood->userId=$identifiedUser->id;

            if (Book::where('id',$bookId)->exists()){
                $userFavoriteGood->bookId=$bookId;
                $userFavoriteBook=UserFavoriteGood::where([['bookId',$bookId],['userId',$identifiedUser->id]]);
                if ($userFavoriteBook->exists()){
                    $userFavoriteBook->delete();
                    return response()->json(['message' => 'the book was removed from the favorites list'], 200);
                }else{
                    return response()->json(['status' => 'error', 'message' => 'This book is not in the user\'s favorite list at all'], 400);
                }
            }else{
                return response()->json(['status' => 'error', 'message' => 'no books were found with this id'], 404);
            }
        }catch (\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function getFavoriteList(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        try{
            $userFavoriteList=UserFavoriteGood::where('userId',$identifiedUser->id);
            if($userFavoriteList->exists()){
               $userFavoriteBooks=$userFavoriteList->join('books','books.id','=','bookId')
                   ->paginate(10);

               return response()->json(['message'=>'user favorite books returned successfully','data'=>$userFavoriteBooks],200);
            }else{
                return response()->json(['status' => 'error', 'message' => 'book not found in user favorites list'], 404);
            }
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }



}
