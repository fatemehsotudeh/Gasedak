<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

use App\Libraries;

class CommentController extends Controller
{
    //
    public function sendComment(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $message=$request->message;
        $rate=$request->rate;
        $bookId=$request->bookId;

        if (empty($bookId)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the bookId field']);
        }

        if (empty($rate) && empty($message)){
            return response()->json(['status' => 'error', 'message' => 'You must fill one of the two rate or message fields']);
        }

        try {
            if (empty($rate)){
                $rate=null;
            }

            //this type of setting prevents duplicate records from being added to the table
            $comment =Comment::firstOrNew([
                'userId' => $identifiedUser->id,
                'bookId' => $bookId,
                'rate' => $rate,
                'message' => $message
            ]);

            if (empty($comment->id)){
                $comment->save();

            }else{
                return response()->json(['status' => 'error', 'message' => 'this comment has already been registered with this information'],422);
            }
            return response()->json(['message' => 'Comment successfully added ,will be displayed after admin approval'],200);

        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }
}

