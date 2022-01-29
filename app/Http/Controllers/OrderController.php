<?php

namespace App\Http\Controllers;

use App\Models\CartHelper;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use App\Libraries;

class OrderController extends Controller
{
    //
    public function addAddressAndShipping(Request $request)
    {
        //get inputs
        $cartId=$request->cartId;
        $shipper=$request->shipper;
        $addressId=$request->addressId;


        //Check that the inputs are not empty
        if (empty($cartId) || empty($shipper) || empty($addressId)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the cartId field']);
        }

        $order=new Order();
        $order->id=$cartId;
        $order->shipper=$shipper;
        $order->addressId=$addressId;

        //create object from cartHelper model
        $cartHelper=new CartHelper();
        $cartHelper->cartId=$cartId;

        //Check the existence of the cart with this id
        $cart=$cartHelper->checkExistenceCart();
        if (!$cart){
            return response()->json(['status'=>'error','message'=>'no cart was found with this id'],404);
        }

        try{
            $order->updateOrderShipperAndAddress();
            return response()->json(['data'=> $order->getOrderData(),'message' => 'update order data successfully'],200);
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function paymentOrder(Request $request)
    {
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //get inputs
        $cartId=$request->cartId;
        $paymentMethod=$request->paymentMethod;

        //Check that the inputs are not empty
        if (empty($cartId) || empty($paymentMethod)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the cartId field']);
        }

        $order=new Order();
        $order->id=$cartId;
        $order->userId=$identifiedUser->id;
        return $order->paymentBasedSelectedMethod($paymentMethod);

    }
}
