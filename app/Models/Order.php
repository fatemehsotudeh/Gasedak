<?php

namespace App\Models;


use http\Env\Request;
use Illuminate\Database\Eloquent\Model;
use function Symfony\Component\VarDumper\Dumper\esc;

class Order extends Model
{
    //
    protected $table='orders';
    protected $fillable=['id','userId','totalPrice','totalDiscountAmount','codeDiscountAmount'];

    public function checkOrderExists()
    {
        if (Order::where('id',$this->id)->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function getOrdersInFourCategory()
    {
        $orders=$this->getAllUserOrders();
        return [
            'waitingForPayment' => $this->relatedCategoryOrderData('waitingForPayment'),
            'progressing' => $this->relatedCategoryOrderData('progressing',$orders),
            'canceled' => $this->relatedCategoryOrderData('canceled',$orders),
            'done' => $this->relatedCategoryOrderData('done',$orders)
        ];
    }

    public function getAllUserOrders()
    {
        return Order::where('orders.userId',$this->userId)->get();
    }

    public function relatedCategoryOrderData($category,$orders=null)
    {
        $orderStatus=new OrderStatus();
        $statusId=$orderStatus->getStatusId($category);
        $relatedOrders=[];

        if ($category=='waitingForPayment'){
            $relatedOrders= Order::where([['orders.userId',$this->userId],['orderStatusId',$statusId]])
                ->join('carts','carts.id','orders.id')
                ->get();
        }else{
            foreach ($orders as $order){
                if ($order['orderStatusId']===$statusId){
                    array_push($relatedOrders,$order);
                }
            }
        }
        return $relatedOrders;
    }

    public function getOrderItem()
    {
        $status=new OrderStatus();
        $cartHelper=new CartHelper();

        $orderStatus=$this->getOrderStatusId();
        $waitingForPaymentStatusId=$status->getStatusId('waitingForPayment');

        $cartHelper->cartId=$this->id;

        if ($orderStatus==$waitingForPaymentStatusId){
            $orderItems=$cartHelper->getCartItemData();
        }else{
            $orderItems=$this->getOrderItemData();
        }

        foreach ($orderItems as $key => $orderItem){
            $cartHelper->bookId=$orderItem['bookId'];
            $orderItems[$key]['imagePath']=$cartHelper->getBookImage();
        }
        return $orderItems;
    }

    public function canCanceledOrder()
    {
        $orderStatus=new OrderStatus();
        $statusId=$orderStatus->getStatusId('progressing');
        $orderStatusId=$this->getOrderStatusId();

        if ($statusId==$orderStatusId){
            $this->setOrderStatusCanceled();
            return true;
        }else{
            return false;
        }
    }

    public function setOrderStatusCanceled()
    {
        $orderStatus=new OrderStatus();
        $canceledId=$orderStatus->getStatusId('canceled');

        Order::where('id',$this->id)
            ->update([
                'orderStatusId' => $canceledId
            ]);
    }

    public function getOrderStatusId()
    {
        return Order::where('id',$this->id)->pluck('orderStatusId')[0];
    }

    public function getOrderItemData()
    {
        return OrderItem::where('orderId',$this->id)
            ->join('books','books.id','orderitems.bookId')
            ->select('orderitems.*','books.name','books.publisher')
            ->get();
    }

    public function updateOrderShipperAndAddress()
    {
        Order::where('id',$this->id)
            ->update([
                'shipperId'=>$this->getShipperId(),
                'userAddressId'=>$this->addressId,
                'discountCodeId'=>null
            ]);
    }

    public function updateOrderPaymentType($method)
    {
        Order::where('id',$this->id)
            ->update([
               'paymentType'=> $method
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
        if ($this->codeDiscountAmount!=0){
            $discount=$this->codeDiscountAmount;
        }else{
            $discount=$this->totalDiscountAmount;
        }
        return  ($this->totalPrice + $this->postCost - $discount);
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
               return $this->checkWallet();
       }
    }

    public function createPayment()
    {
        $payment=new Payment();
        $payment->userId=$this->userId;
        $payment->amount=$this->getOrderTotalCost();
        $payment->orderId=$this->id;

        return $payment->instantPayment();
    }

    public function checkWallet()
    {
        $wallet=Wallet::where('userId',$this->userId);
        if ($wallet->exists()){
            $balance=$wallet->pluck('balance')[0];
            $orderCosts=$this->getOrderTotalCost();
            if ($balance>=$orderCosts){
                return true;
            }
            return false;
        }
        return false;
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
                $data='';
                switch ($paymentMethod){
                    case 'instant':
                        if ($result['status']=='error'){
                            $message=$result['message'];
                            $statusCode=500;
                        }else{
                            $data=$result['paymentLink'];
                            $message='return payment link successFully';
                        }
                        break;
                    case 'wallet':
                        if ($result){
                            $message='payment from the wallet was successful';
                        }else{
                            $message='wallet balance is not enough';
                            $statusCode=400;
                        }
                        break;
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

        if ($statusCode!=200){
            return response()->json(['status'=> 'error','message' => $message],$statusCode);
        }

        if ($cartHelper->getCartQuantity()==0){
            return response()->json(['status'=> 'error','message' => 'متاسفانه همه کتاب های انتخاب شده ناموجود شده است '],400);
        }

        if(sizeof($cartHelper->orderProcessMessages)!=0){
            $responseMessage=$cartHelper->orderProcessMessages;
            $responseData=$data;
            if ($paymentMethod=='wallet'){
                $statusCode=400;
            }
        }else{
            if ($paymentMethod=='wallet'){
                $responseMessage=$message;
                $responseData=$data;
                $this->updateOrderStatus();
                $this->updateDiscountCodes();
                $this->decreaseWalletBalance();
                $this->deleteCartAndUpdateOrder();
            }else{
                $responseMessage=$message;
                $responseData=$data;
            }
        }

        if ($statusCode!=400){
            return response()->json(['data'=> $responseData,'message' => $responseMessage],$statusCode);
        }else{
            return response()->json(['message' => $responseMessage],$statusCode);
        }
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
        $cartItems=CartItem::where([['cartId',$this->id],['isAvailable',1]])->get();
        $disTypeCode=$this->checkDisTypeCode();

        foreach ($cartItems as $cartItem){
            $orderItem = OrderItem::firstOrNew([
                'orderId' => $this->id,
                'bookId' => $cartItem['bookId'],
                'price' => $cartItem['price'],
                'discountAmount' => $cartItem['discountAmount'],
                'quantity' => $cartItem['quantity']
            ]);

            $orderItem->save();
            $storeId=$this->getCartStoreId();
            $this->decreaseGoodsInventoryAndUpdateBookPurchaseCount($cartItem['bookId'],$cartItem['quantity'],$storeId);
            if (!$disTypeCode && $cartItem['isDaily']){
                $this->decreaseGoodsDailyDiscountCount($cartItem['bookId'],$cartItem['quantity'],$storeId);
            }

        }
        $this->updateOrderQPDAndUpdateStorePurchaseCount();
    }

    public function updateOrderQPDAndUpdateStorePurchaseCount()
    {
        $cart=Cart::where('id',$this->id)->first();
        $storeId=$this->getCartStoreId();

        Order::where('id',$this->id)
            ->update([
                'totalPrice' => $cart['totalPrice'],
                'totalQuantity' => $cart['totalQuantity'],
                'totalDiscountAmount' => $cart['totalDiscountAmount']
            ]);

        $this->updateStorePurchaseCount($storeId,$cart['totalQuantity']);
    }

    public function updateStorePurchaseCount($storeId,$quantity)
    {
        $store=Store::where('id',$storeId);
        $oldPurchaseCount=$store->pluck('purchaseCount')[0];
        $newPurchaseCount=$oldPurchaseCount+$quantity;
        $store->update([
            'purchaseCount' => $newPurchaseCount
        ]);

    }

    public function updateOrderStatus($depositId=null)
    {
        $order=new OrderStatus();
        Order::where('id',$this->id)
            ->update([
                'orderStatusId' => $order->getStatusId('progressing'),
                'orderDate' => date('Y-m-d H:i:s'),
                'isPaid' => true,
                'paymentId' => $depositId
            ]);
    }

    public function updateDiscountCodes()
    {
        $disCodeId=$this->getOrderDiscountCodeId();
        if ($this->getOrderDiscountCodeId()!=null){
            $discount=new Discount();
            $discount->orderId=$this->id;
            $discount->id=$disCodeId;
            $discount->updateDiscountUsed();
            $this->discountType='Code';
        }else{
            $this->discountType='other';
        }
    }

    public function decreaseWalletBalance()
    {
        $wallet=Wallet::where('userId',$this->userId);
        $balance=$wallet->pluck('balance')[0];
        $orderCost=$this->getOrderTotalCost();

        $wallet->update([
            'balance' => $balance-$orderCost
        ]);
    }

    public function deleteCartAndUpdateOrder()
    {
        $order=new Order();
        $order->id=$this->id;
        $order->addOrderItems();

        $cartHelper=new CartHelper();
        $cartHelper->cartId=$this->id;
        $cartHelper->deleteCartItems();
        $cartHelper->deleteCart();
    }

    public function checkDisTypeCode()
    {
        $disType=$this->discountType;
        if ($disType=='code'){
            return true;
        }else{
            return false;
        }
    }

    public function decreaseGoodsInventoryAndUpdateBookPurchaseCount($bookId,$quantity,$storeId)
    {
        $book=StoreBook::where([['bookId',$bookId],['storeId',$storeId]]);
        $bookInventory=$book->pluck('inventory')[0];
        $newInventory=$bookInventory-$quantity;
        if ($newInventory<0){
            $newInventory=0;
        }
        $book->update([
            'inventory' => $newInventory
        ]);

        $this->updateBookPurchaseCount($bookId,$quantity);
    }

    public function updateBookPurchaseCount($bookId,$quantity)
    {
        $book=Book::where('id',$bookId);
        $oldPurchaseCount=$book->pluck('purchaseCount')[0];
        $newPurchaseCount=$oldPurchaseCount+$quantity;
        $book->update([
            'purchaseCount' => $newPurchaseCount
        ]);
    }

    public function decreaseGoodsDailyDiscountCount($bookId,$quantity,$storeId)
    {
        $book=StoreBook::where([['bookId',$bookId],['storeId',$storeId]]);
        $bookDailyCount=$book->pluck('dailyCount')[0];
        $newDailyCount=$bookDailyCount-$quantity;
        if ($newDailyCount<0){
            $newDailyCount=0;
        }
        $book->update([
            'dailyCount' => $newDailyCount
        ]);
    }

    public function getCartStoreId()
    {
        return Cart::where('id',$this->id)->pluck('storeId')[0];
    }

}
