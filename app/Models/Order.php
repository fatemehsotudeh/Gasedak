<?php

namespace App\Models;


use http\Env\Request;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $table='orders';
    protected $fillable=['id','userId','totalPrice','totalDiscountAmount','codeDiscountAmount'];

    public function updateOrderShipperAndAddress()
    {
        Order::where('id',$this->id)
            ->update([
               'shipperId'=>$this->getShipperId(),
                'userAddressId'=>$this->addressId
            ]);
    }

    public function getShipperId()
    {
        return Shipper::where('shipperName',$this->shipper)->first()['id'];
    }

    public function setOrderQPD()
    {
        $cart=Cart::where('id',$this->id)->first();
        $this->totalPrice=$cart['totalPrice'];
        $this->totalDiscountAmount=$cart['totalDiscountAmount'];
        $this->totalQuantity=$cart['totalQuantity'];
    }

    public function getOrderData()
    {
        $this->setOrderCosts();
        return [
            'postCost' => $this->postCost,
            'totalQuantity' => $this->totalQuantity,
            'totalPrice' => $this->totalPrice,
            'totalDiscountAmount' => $this->totalDiscountAmount
            ];
    }

    public function getOrderTotalCost()
    {
        $this->setOrderCosts();
        return  ($this->totalPrice + $this->postCost - $this->totalDiscountAmount-$this->codeDiscountAmount);
    }

    public function setOrderCosts()
    {
        $this->setOrderQPD();
        $setting=new Setting();
        $this->postCost=$setting->getPostCost();
    }

    public function paymentBasedSelectedMethod($method)
    {
       switch ($method){
           case 'instant':
               return $this->createPayment();
           case 'wallet':
               return '';
       }
    }

    public function createPayment()
    {
        $payment=new Payment();
        $payment->userId=$this->userId;
        $payment->amount=$this->getOrderTotalCost();
        $payment->orderId=$this->orderId;
        return $payment->instantPayment();
    }

    public function checkAndUpdate($cartHelper)
    {
        $cartHelper->checkAndUpdateCartItem();
        $cartHelper->updateCartQPD();
    }

    public function checkAndGetRelatedResponse($cartHelper,$state=null,$paymentMethod=null,$discount=null)
    {
        $this->checkAndUpdate($cartHelper);

        $statusCode=200;
        switch ($state){
            case 'payment':
                $this->orderId=$cartHelper->cartId;
                $this->createDiscountObject($cartHelper);
                $result=$this->paymentBasedSelectedMethod($paymentMethod);

                if ($result['status']=='error'){
                    $data='';
                    $message=$result['message'];
                    $statusCode=500;
                }else{
                    $data=$result['paymentLink'];
                    $message='return payment link successFully';
                }
                break;

            case 'discount':
                $data=$discount->getDiscountResult($this->id);
                $message='register discount code successfully';
                break;

            default:
                $data=$this->getOrderData();
                $message='update order data successfully';
        }

        if ($statusCode==500){
            return response()->json(['status'=> 'error','message' => $message],500);
        }

        if ($cartHelper->getCartQuantity()==0){
            return response()->json(['status'=> 'error','message' => 'متاسفانه همه کتاب های انتخاب شده ناموجود شده است '],400);
        }

        if(sizeof($cartHelper->orderProcessMessages)!=0){
            return response()->json(['data'=> $data,'message' => $cartHelper->orderProcessMessages],200);
        }
        return response()->json(['data'=> $data,'message' => $message],200);
    }

    public function saveDiscountCodeId($codeId)
    {
        Order::where('id',$this->id)
            ->update([
                'discountCodeId'=>$codeId
            ]);
    }

    public function getOrderDiscountCodeId()
    {
        return Order::where('id',$this->id)->pluck('discountCodeId')[0];
    }

    public function createDiscountObject($cartHelper)
    {
        $discountCodeId=$this->getOrderDiscountCodeId();
        if ($discountCodeId==null){
             $this->codeDiscountAmount=0;
        }else{
            $discount=new Discount();
            $discount->id=$discountCodeId;
            $discount->orderId=$cartHelper->cartId;
            $this->codeDiscountAmount=$discount->checkOrderDiscountCodeAndGetResult();

            if ($this->codeDiscountAmount==0){
               $this->saveDiscountCodeId(null);
            }
        }
    }

    public function addOrderItems()
    {
        $cartItems=CartItem::where('cartId',$this->id)->get();

        foreach ($cartItems as $cartItem){
            $orderItem = OrderItem::firstOrNew([
                'orderId' => $this->id,
                'bookId' => $cartItem['bookId'],
                'price' => $cartItem['price'],
                'discountAmount' => $cartItem['discountAmount'],
                'quantity' => $cartItem['quantity']
            ]);
            $orderItem->save();
        }

        $this->updateOrderQPD();
    }

    public function updateOrderQPD()
    {
        $cart=Cart::where('id',$this->id)->first();

        Order::where('id',$this->id)
            ->update([
                'totalPrice' => $cart['totalPrice'],
                'totalQuantity' => $cart['totalQuantity'],
                'totalDiscountAmount' => $cart['totalDiscountAmount']
            ]);
    }


}
