<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Libraries;

class Discount extends Model
{
    //
    protected $table='discountcodes';
    protected $fillable=['userId','code','id','bookId','discountRow','orderId','discountRef','message'];
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
                return $this->checkDiscountRef();
            case 'specified':
                return $this->checkUserId();
        }
    }

    public function checkUserId()
    {
        $userId=$this->discountRow['userId'];

        if ($this->userId==$userId){
            return $this->checkDiscountRef();
        }else{
            return 'The code entered is not for you';
        }
    }

    public function checkDiscountRef()
    {
        $this->discountRef=$this->discountRow['discountRef'];

        switch ($this->discountRef){
            case 'book':
                return $this->checkBookInCart();
            case 'cart':
            case 'sum':
            case 'delivery':
                return $this->checkStoreRef();
        }
    }

    public function checkBookInCart()
    {
        $this->bookId=$this->discountRow['bookId'];

        $cartItem=$this->createCartModel($this->bookId);

        if($cartItem->checkBookInCartItem()){
            return $this->checkStoreRef();
        }else{
            $this->message='this discount code is for books, but you do not have this book in your cart';
        }
    }

    public function checkStoreRef()
    {
        $storeRef=$this->discountRow['storeRef'];

        switch ($storeRef){
            case 'all':
                $price=$this->getRelatedRefPrice();
                return $this->checkAndGetDiscountAmount($price);
            case 'one':
                $price=$this->getRelatedRefPrice();
                return $this->checkStoreAndGetDiscountAmount($price);
            case 'many':
                $price=$this->getRelatedRefPrice();
                return $this->checkStoresAndGetDiscountAmount($price);
        }
    }

    public function getRelatedRefPrice()
    {
        switch ($this->discountRef){
            case 'book':
                $cart=$this->createCartModel($this->bookId);
                return $cart->getCartItemQPD()['price'];
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
        if($this->checkPriceBetweenUpAndLow($price)){
            //update cart item and discount code used field set 1 and increase count
            $disAmount=$this->discountRow['amount'];
            $discountType=$this->discountRow['discountType'];

            if ($discountType=='percentage'){
                return ($disAmount * $price )/100;
            }else{
                return $disAmount;
            }
        }else{
           $this->message='minimum price to apply this discount code is'.$this->discountRow['upperBound'];
        }
    }

    public function checkPriceBetweenUpAndLow($price)
    {
        $upperBound=$this->discountRow['upperBound'];
        $loweBound=$this->discountRow['lowerBound'];

        if($upperBound>=$price && $loweBound<=$price){
            return true;
        }else{
            return false;
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
            return $this->checkAndGetDiscountAmount($price);
        }else{
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
            return $this->checkAndGetDiscountAmount($price);
        }else{
            $this->message='this is not a discount code for this store';
        }
    }
}
