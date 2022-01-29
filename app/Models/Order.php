<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $table='orders';
    protected $fillable=['userId','totalPrice','totalDiscountAmount'];

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

    public function setOrderPD()
    {
        $cart=Cart::where('id',$this->id)->first();
        $this->totalPrice=$cart['totalPrice'];
        $this->totalDiscountAmount=$cart['totalDiscountAmount'];
    }

    public function getOrderData()
    {
        $this->setOrderCosts();
        return [
            'postCost' => $this->postCost,
            'totalPrice' => $this->totalPrice,
            'totalDiscountAmount' => $this->totalDiscountAmount
            ];
    }

    public function getOrderTotalCost()
    {
        $this->setOrderCosts();
        return  ($this->totalPrice + $this->postCost - $this->totalDiscountAmount);
    }

    public function setOrderCosts()
    {
        $this->setOrderPD();
        $setting=new Setting();
        $postCost=$setting->getPostCost();
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
        return $payment->instantPayment();
    }


}
