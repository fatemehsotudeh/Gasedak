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
            return $order->checkAndGetRelatedResponse($cartHelper);
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function getOrderCosts(Request $request)
    {
        //get input
        $cartId=$request->cartId;

        //Check that the input are not empty
        if (empty($cartId)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the cartId field']);
        }

        //create object from cartHelper model
        $cartHelper=new CartHelper();
        $cartHelper->cartId=$cartId;

        //Check the existence of the cart with this id
        $cart=$cartHelper->checkExistenceCart();
        if (!$cart){
            return response()->json(['status'=>'error','message'=>'no cart was found with this id'],404);
        }

        $order=new Order();
        $order->id=$cartId;

        try{
            return $order->checkAndGetRelatedResponse($cartHelper);
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
            return response()->json(['status' => 'error', 'message' => 'You must fill the fields']);
        }

        $order=new Order();
        $order->id=$cartId;
        $order->userId=$identifiedUser->id;

        //create object from cartHelper model
        $cartHelper=new CartHelper();
        $cartHelper->cartId=$cartId;

        //Check the existence of the cart with this id
        $cart=$cartHelper->checkExistenceCart();
        if (!$cart){
            return response()->json(['status'=>'error','message'=>'no cart was found with this id'],404);
        }

        try {
            $order->updateOrderPaymentType($paymentMethod);
            return $order->checkAndGetRelatedResponse($cartHelper,'payment',$paymentMethod);
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function getOrderData(Request $request)
    {
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $order=new Order();
        $order->userId=$identifiedUser->id;

        try {
              $data=$order->getOrdersInFourCategory();
              return response()->json(['data'=>$data,'message'=>'return orders successfully'],200);
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function getOrderItemData(Request $request)
    {
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //get input
        $orderId=$request->orderId;

        //Check that the inputs are not empty
        if (empty($orderId)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the orderId field']);
        }

        $order=new Order();
        $order->id=$orderId;

        if (!$order->checkOrderExists()){
            return response()->json(['status' => 'error', 'message' => 'no order with this id found'],404);
        }

        try {
             $data=$order->getOrderItem();
            return response()->json(['data'=>$data,'message'=>'return order items successfully'],200);
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function cancelOrder(Request $request)
    {
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        //get input
        $orderId=$request->orderId;

        //Check that the inputs are not empty
        if (empty($orderId)){
            return response()->json(['status' => 'error', 'message' => 'You must fill the orderId field']);
        }

        $order=new Order();
        $order->id=$orderId;

        if (!$order->checkOrderExists()){
            return response()->json(['status' => 'error', 'message' => 'no order with this id found'],404);
        }

        try {
            if ($order->canCanceledOrder()) {
                return response()->json(['message'=> 'canceled order successfully'],200);
            }else{
                 return response()->json(['status'=>'error','message'=> 'this order can not be canceled'],400);
            }
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }

    }

}
