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
        $order->orderDate=$helper->getCurrentDate();
        $order->orderStatusId=1;

        $order->save();
    }

    public function deleteOrder()
    {
        Order::where('id',$this->cartId)->delete();
    }


}
