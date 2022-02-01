<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Libraries;

class OrderHelper extends Model
{
    //
    protected $fillable=[
        'userId','storeId','cartId'
    ];

    public function createOrder()
    {
        //generate order code
        $helper=new Libraries\Helper();
        $code=$helper->generateAlphaNumericCode(12);

        $order=new Order();
        $order->userId=$this->userId;
        $order->storeId=$this->storeId;
        $order->trackingCode=$code;
        //date_default_timezone_set('Asia/Tehran');
        $order->orderDate=$helper->getCurrentDate();
        $order->orderStatusId=$this->getStatusId('waitingForPayment');

        while (Order::where('trackingCode',$code)->exists()){
            $code=$helper->generateAlphaNumericCode(12);
        }

        $order->save();
    }

    public function deleteOrder()
    {
        $order=Order::where('id',$this->cartId);

        if($order->exists()){
            $order->delete();
        }
    }

    public function getStatusId($status)
    {
        return OrderStatus::where('statusName',$status)->pluck('id')[0];
    }

}
