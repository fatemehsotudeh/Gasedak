<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Libraries;

class Discount extends Model
{
    //
    protected $table='discountcodes';
    protected $fillable=['userId','code','id','bookId','discountRow','orderId','discountRef','message','amount'];
    protected $casts=[
        'storesId'=> 'array'
    ];

    public function checkExistenceCode()
    {
        $discount=Discount::where('code',$this->code);
        $this->discountRow=$discount->first();

        if($discount->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function setDiscountRow()
    {
        $this->discountRow=Discount::where('id',$this->id)->first();
    }

    public function checkCodeExpiration()
    {
        $helper=new Libraries\Helper();
        $expDate=$this->discountRow['expDate'];
        $currentDate=$helper->getCurrentDate();

        $useLimit=$this->discountRow['useLimit'];
        $usedCount=$this->discountRow['usedCount'];

        if ($expDate<$currentDate || $useLimit<=$usedCount){
            return true;
        }else{
            return false;
        }
    }

    public function isPublicCode()
    {
        $isPublic=$this->discountRow['public'];

        if ($isPublic){
            return true;
        }else{
            return false;
        }
    }

    public function checkCodeUserType()
    {
        $userType=$this->discountRow['userType'];

        switch ($userType){
            case 'all':
            case 'public':
                $this->checkDiscountRef();
                break;
            case 'specified':
                $this->checkUserId();
        }
    }

    public function checkUserId()
    {
        $userId=$this->discountRow['userId'];

        if ($this->userId==$userId){
            $this->checkDiscountRef();
        }else{
            $this->amount=0;
            $this->message='The code entered is not for you';
        }
    }

    public function checkDiscountRef()
    {
        $this->discountRef=$this->discountRow['discountRef'];

        switch ($this->discountRef){
            case 'book':
                $this->checkBookInCart();
                break;
            case 'cart':
            case 'sum':
            case 'delivery':
                $this->checkStoreRef();
        }
    }

    public function checkBookInCart()
    {
        $this->bookId=$this->discountRow['bookId'];

        $cartItem=$this->createCartModel($this->bookId);

        if($cartItem->checkAvailableBookInCartItem()){
            $this->checkStoreRef();
        }else{
            $this->amount=0;
            $this->message='this discount code is for books, but you do not have this book in your cart';
        }
    }

    public function checkStoreRef()
    {
        $storeRef=$this->discountRow['storeRef'];

        switch ($storeRef){
            case 'all':
                $price=$this->getRelatedRefPrice();
                $this->checkAndGetDiscountAmount($price);
                break;
            case 'one':
                $price=$this->getRelatedRefPrice();
                $this->checkStoreAndGetDiscountAmount($price);
                break;
            case 'many':
                $price=$this->getRelatedRefPrice();
                $this->checkStoresAndGetDiscountAmount($price);
                break;
        }

    }

    public function getRelatedRefPrice()
    {
        switch ($this->discountRef){
            case 'book':
                $cart=$this->createCartModel($this->bookId);
                return $cart->getCartItemQPD()['price']*$cart->getCartItemQPD()['quantity'];
            case 'cart':
                $cart=$this->createCartModel();
                return $cart->getCartQPD()['totalPrice'];
            case 'sum':
                $cart=$this->createCartModel();
                $setting=new Setting();
                return $cart->getCartQPD()['totalPrice']+$setting->getPostCost();
            case 'delivery':
                $setting=new Setting();
                return $setting->getPostCost();
        }
    }

    public function checkAndGetDiscountAmount($price)
    {
        $discountType=$this->discountRow['discountType'];
        $disAmount=$this->discountRow['amount'];

        if ($discountType=='percentage'){
            $dis= ($price * $disAmount )/100;
            $disAfterCheck=$this->checkDisLowThanUpperBoundAndGetResult($dis);

            if ($disAfterCheck<$this->getTotalDiscountAmount()){
                $this->message='کاربر عزیر میزان تخفیف های قبلی از  کد تخفیف شما بیشتر است پیشنهاد ما به شما این است که در خرید های بعدی تان از این کد استفاده کنید';
                $this->amount=0;
            }else{
                $this->amount=$disAfterCheck;
            }
        }else{
            $dis = $disAmount;
            if($this->checkPriceHighThanLowerBound($price)) {
                $disAfterCheck = $disAmount;
                if ($dis<$this->getTotalDiscountAmount()){
                    $this->message='کاربر عزیر میزان تخفیف های قبلی از  کد تخفیف شما بیشتر است پیشنهاد ما به شما این است که در خرید های بعدی تان از این کد استفاده کنید';
                    $this->amount=0;
                }else{
                    $this->amount=$disAfterCheck;
                }
            }else{
                $this->message = 'حداقل قیمت برای اعمال این کد باید' . $this->discountRow['lowerBound'] . 'باشد';
                $this->amount=0;
            }
        }

    }

//        if($this->checkPriceHighThanLowerBound($price)){
//            //update cart item and discount code used field set 1 and increase count
//            $disAmount=$this->discountRow['amount'];
//            $discountType=$this->discountRow['discountType'];
//
//            if ($discountType=='percentage'){
//                $dis= ($disAmount * $price )/100;
//            }else{
//                $dis=$disAmount;
//            }
//            $dis=$this->checkDisLowThanUpperBoundAndGetResult($dis);
//            if ($dis<$this->getTotalDiscountAmount()){
//                $this->message='کاربر عزیر میزان تخفیف های قبلی از  کد تخفیف شما بیشتر است پیشنهاد ما به شما این است که در خرید های بعدی تان از این کد استفاده کنید';
//                $this->amount=0;
//            }else{
//                $this->amount=$dis;
//            }
//        }else{
//           $this->message='حداقل قیمت برای اعمال این کد باید'.$this->discountRow['lowerBound'].'باشد';
//           $this->amount=0;
//        }


    public function checkPriceHighThanLowerBound($price)
    {
      //  $upperBound=$this->discountRow['upperBound'];
        $loweBound=$this->discountRow['lowerBound'];

        if($loweBound<=$price){
            return true;
        }else{
            return false;
        }
    }

    public function checkDisLowThanUpperBoundAndGetResult($discountAmount)
    {
        $upperBound=$this->discountRow['upperBound'];

        if($upperBound<=$discountAmount){
            return $upperBound;
        }else{
            return $discountAmount;
        }
    }

    public function createCartModel($bookId=null,$storeId=null)
    {
        $cartItem=new CartHelper();
        $cartItem->cartId=$this->orderId;
        $cartItem->storeId=$storeId;
        $cartItem->bookId=$bookId;

        return $cartItem;
    }

    public function checkStoreAndGetDiscountAmount($price)
    {
        $storesId=$this->discountRow['storesId'];

        $cart=$this->createCartModel(null,$storesId[0]);

        if ($cart->checkStoreInCartV2()){
            $this->checkAndGetDiscountAmount($price);
        }else{
           $this->amount=0;
           $this->message='this is not a discount code for this store';
        }

    }

    public function checkStoresAndGetDiscountAmount($price)
    {
        $storesId=$this->discountRow['storesId'];
        $flag=0;
        foreach ($storesId as $storeId){
            $cart=$this->createCartModel(null,$storeId);
            if ($cart->checkStoreInCartV2()){
                $flag=1;
            }
        }

        if ($flag==1){
            $this->checkAndGetDiscountAmount($price);
        }else{
            $this->message='this is not a discount code for this store';
            $this->amount=0;
        }
    }

    public function getDiscountResult($cartId)
    {
        $order=new Order();
        $order->id=$cartId;

       $result=$order->getOrderData();
       $result['totalDiscountAmount']=$this->amount;

       return $result;
    }

    public function checkOrderDiscountCodeAndGetResult()
    {
        $this->setDiscountRow();
        if (!$this->checkCodeExpiration()){
            $this->checkCodeUserType();
            return $this->amount;
        }else{
            return 0;
        }
    }

    public function updateDiscountUsed()
    {
        $this->setDiscountRow();
        Discount::where('id',$this->id)
            ->update([
                'isUsed' => true,
                'usedCount'=> $this->discountRow['usedCount']+1
            ]);
    }

    public function getTotalDiscountAmount()
    {
        return Cart::where('id',$this->orderId)->pluck('totalDiscountAmount')[0];
    }

}
