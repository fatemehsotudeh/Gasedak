<?php

namespace App\Http\Controllers;

use App\Models\CartHelper;
use App\Models\Discount;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use App\Libraries;

class DiscountController extends Controller
{
    //
    public function registerDiscountCode(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //get inputs
        $cartId=$request->cartId;
        $code=$request->code;

        //Check that the inputs are not empty
        if (empty($cartId) || empty($code)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the fields']);
        }

        //create object from cartHelper model
        $cartHelper=new CartHelper();
        $cartHelper->cartId=$cartId;

        //Check the existence of the cart with this id
        $cart=$cartHelper->checkExistenceCart();
        if (!$cart){
            return response()->json(['status'=>'error','message'=>'no cart was found with this id'],404);
        }

        $discount=new Discount();
        $discount->code=$code;
        $discount->orderId=$cartId;
        $discount->userId=$identifiedUser->id;


        if ($discount->checkExistenceCode()){
            //first check if the code is expired or not
            if (!$discount->checkCodeExpiration()){
               $discount->checkCodeUserType();
                $order=new Order();
                $order->id=$cartId;
                $result=$order->checkAndGetRelatedResponse($cartHelper,'discount','',$discount);

                if (array_key_exists('status',$result->getOriginalContent())){
                    return $result;
                }else{
                    if ($message=$discount->message){
                        return response()->json(['status'=>'error','message'=>$message],403);
                    }else{
                        //save code id in order table
                        $order->saveDiscountCodeId($discount->discountRow['id']);
                        return $result;
                    }
                }
            }else{
                return response()->json(['status'=>'error','message'=>'the code entered is expired'],403);
            }
        }else{
            return response()->json(['status'=>'error','message'=>'the code entered is incorrect'],401);
        }
    }
}
