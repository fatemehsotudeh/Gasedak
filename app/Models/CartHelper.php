<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Libraries;

use Morilog\Jalali\Jalalian;

class CartHelper extends Model
{
    //
    protected $fillable=[
        'userId','bookId','storeId','cartId','bookRecord','isDaily'
    ];

    protected $attributes=[
        'quantityChangeMessages' => [],
        'priceChangeMessages' => [],
        'orderProcessMessages'=>[],
    ];

    public function checkExistenceStore()
    {
        if(Store::where('id',$this->storeId)->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function checkExistenceCart()
    {
        if(Cart::where('id',$this->cartId)->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function checkExistenceBookInCart()
    {
        if(CartItem::where([['cartId',$this->cartId],['bookId',$this->bookId]])->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function checkExistenceBookStore()
    {
        if(StoreBook::where([['storeId',$this->storeId],['bookId',$this->bookId]])->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function checkStoreNotSuspendedOrClose()
    {
        if(Store::where([['id',$this->storeId],['isOpen',true],['isSuspended',false]])->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function checkStoreInCart()
    {
        $cart=Cart::where([['storeId',$this->storeId],['userId',$this->userId]]);
        if($cart->exists()){
            $this->cartId=$cart->pluck('id')[0];
            return true;
        }else{
            return false;
        }
    }

    public function checkStoreInCartV2()
    {
        if(Cart::where([['storeId',$this->storeId],['id',$this->cartId]])->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function checkBookInCart()
    {
        return $this->checkBookInCartItem();
    }

    public function checkBookInCartItem()
    {
        $cartItem=CartItem::where([
            ['cartId',$this->cartId],
            ['bookId',$this->bookId]
        ]);

        if($cartItem->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function checkAvailableBookInCartItem()
    {
        $cartItem=CartItem::where([
            ['cartId',$this->cartId],
            ['bookId',$this->bookId],
            ['isAvailable',1]
        ]);

        if($cartItem->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function createNewCart()
    {
        $cart=new Cart();
        $cartItem=new CartItem();

        $cart->userId=$this->userId;
        $cart->storeId=$this->storeId;
        $cart->save();

        $cartItem->cartId=$cart->id;
        $cartItem->bookId=$this->bookId;
        $cartItem->price=$this->getBookPrice();
        $cartItem->discountAmount=$this->getBookDiscount();
        $cartItem->save();

        $this->cartId=$cart->id;
        $this->updateCartQPD();
    }

    public function updateCart()
    {
        $cartItem=new CartItem();

        $cartItem->cartId=$this->cartId;
        $cartItem->bookId=$this->bookId;
        $cartItem->price=$this->getBookPrice();
        $cartItem->discountAmount=$this->getBookDiscount();
        $cartItem->save();
    }

    public function createOrUpdateCart($resultStoreInCart)
    {
        if ($resultStoreInCart){
            $resultBookInCart=$this->checkBookInCart();
            if ($resultBookInCart){
                return false;
            }else{
                $this->updateCart();
                $this->updateCartQPD();
                $this->updateOrderDate();
                return true;
            }
        }else{
            $this->createNewCart();
            $this->createOrderForCart();
            return true;
        }
    }

    public function getCartId()
    {
        return Cart::where([['userId',$this->userId],['storeId',$this->storeId]])->pluck('id')[0];
    }

    public function checkInventory()
    {
        $inventory=$this->getBookInventory();
        if ($inventory!=0){
            return true;
        }else{
            return false;
        }
    }

    public function getBookData()
    {
        $this->bookRecord=StoreBook::where([['bookId',$this->bookId],['storeId',$this->storeId]])->first();
    }

    public function getBookInventory()
    {
        $this->getBookData();
        return $this->bookRecord['inventory'];
    }

    public function getBookPrice()
    {
        return  $this->bookRecord['price'];
    }

    public function createOrderForCart()
    {
        $orderHelper=new OrderHelper();
        $orderHelper->userId=$this->userId;
        $orderHelper->storeId=$this->storeId;
        $orderHelper->createOrder();
    }

    public function updateOrderDate()
    {
        $helper=new Libraries\Helper();

        $order=new Order();
        //date_default_timezone_set('Asia/Tehran');
        $orderDate=$helper->getCurrentDate();

        $order->where('id',$this->cartId)
            ->update(['orderDate'=>$orderDate]);
    }

    public function getCartsData()
    {
        return $this->getCartLists();
    }

    public function checkAndGetCartData()
    {
        $this->checkAndUpdateCartItem();
        $this->updateCartQPD();
        return $this->getCartItems();
    }

    public function getCartItems()
    {
        $cart=Cart::where('carts.id',$this->cartId)->first();

        $cart['books']=$this->getCartItemData();

        $counter=0;
        foreach ($cart['books']->toArray() as $index=>$book){
            $this->bookId=$book['id'];
            $cart['books'][$index]['image']=$this->getBookImage();
            $cart['books'][$index]['quantityChangeMessage']=$this->quantityChangeMessages[$counter];
            $cart['books'][$index]['priceChangeMessage']=$this->priceChangeMessages[$counter];
            $counter++;
        }

        return $cart;
    }

    public function getCartLists()
    {
       $carts=Cart::where('carts.userId',$this->userId)
           ->join('stores','stores.id','carts.storeId')
           ->Join('orders','orders.id','carts.id')
           ->select('carts.id','orders.orderDate','orders.trackingCode','stores.name')
           ->get();


       foreach ($carts as $key=>$cart){
           $this->cartId=$cart['id'];
           $booksId=$this->getBookIdFromCartsItem();
           $goodsImage=[];
           foreach ($booksId as $bookId){
               $this->bookId=$bookId;
               array_push($goodsImage,$this->getBookImage());
           }
           $carts[$key]['orderDate'] = jdate($cart['orderDate'])->format('Y-m-d');
           $carts[$key]['goodsImage']=$goodsImage;
       }

        return $carts;
    }

    public function getBookImage()
    {
        $imagePath=BookImage::where('bookId',$this->bookId);
       if($imagePath->exists()){
           return $imagePath->first()['imagePath'];
       }else{
           return "";
       }
    }

    public function getCartItemData()
    {
        return CartItem::where('cartId',$this->cartId )
            ->join('books','books.id','cartitems.bookId')
            ->select('cartitems.*','books.name','books.publisher','books.authors')
            ->get();
    }

    public function getBookIdFromCartsItem()
    {
        return CartItem::where('cartId',$this->cartId)->pluck('bookId');
    }

    public function getCartItemQPD()
    {
        return CartItem::where([['cartId',$this->cartId],['bookId',$this->bookId]])
            ->first();
    }

    public function getUserCartsId()
    {
        return Cart::where('userId',$this->userId)
            ->pluck('id');
    }

    public function checkAndUpdateCartItem()
    {
       $this->storeId=$this->getCartStoreId();
       $bookIds=$this->getCartItemsBookId();
       $cartItemsQuantity=$this->getCartItemsQuantity();


       foreach ($bookIds as $key => $bookId){
           $this->bookId=$bookId;
           $inventory=$this->getBookInventory();
           $cartItemQuantity=$cartItemsQuantity[$key];
           $checkResult=$this->checkCartItemQuantityWithInventory($cartItemQuantity,$inventory);
           $this->setChangeQuantityMessages($checkResult['stateQuantityChange']);
           $this->updateCartItemQPD($checkResult['quantity'],$checkResult['isAvailable']);
       }

    }

    public function checkCartItemQuantityWithInventory($cartItemQuantity,$inventory)
    {
        $isAvailable=1;
        if ($inventory==0 && $cartItemQuantity!=0){
            $quantity=0;
            $isAvailable=0;
            $stateQuantityChange='unavailable';
        }elseif($cartItemQuantity>$inventory){
            $quantity=$inventory;
            $stateQuantityChange='change';
        }elseif ($cartItemQuantity==0){
            // If it was not available before,
            // we will check to see if it is available,
            // inform the user and set quantity one and make it available.
            if ($inventory>0){
                $quantity=1;
                $stateQuantityChange='available';
            }else{
                $isAvailable=0;
                $quantity=$cartItemQuantity;
                $stateQuantityChange='withOutChange';
            }
        }else{
            $quantity=$cartItemQuantity;
            $stateQuantityChange='withOutChange';
        }

        return [
            'quantity'=> $quantity,
            'stateQuantityChange'=> $stateQuantityChange,
            'isAvailable'=> $isAvailable
        ];
    }

    public function getCartStoreId()
    {
        return Cart::where('id',$this->cartId)->pluck('storeId')[0];
    }

    public function updateCartQPD()
    {
        Cart::where([
            ['id',$this->cartId],
        ])->update([
            'totalQuantity'=> $this->calculateTotalQuantity(),
            'totalPrice'=>$this->calculateTotalPrice(),
            'totalDiscountAmount'=>$this->calculateTotalDiscount()
        ]);
    }

    public function getCartQPD()
    {
        return Cart::where('id',$this->cartId)
            ->first();
    }

    public function getCartItemQuantity()
    {
        return CartItem::where([['cartId',$this->cartId],['bookId',$this->bookId]])->pluck('quantity')[0];
    }

    public function calculateTotalPrice()
    {
        $cartItem=CartItem::where('cartId',$this->cartId);
        if (sizeof($cartItem->get())!=1){
            return $cartItem->sum(CartItem::raw('price * quantity'));
        }else{
            return $cartItem->first()['quantity']*$cartItem->first()['price'];
        }

    }

    public function calculateTotalDiscount()
    {
        $cartItem=CartItem::where('cartId',$this->cartId);
        if (sizeof($cartItem->get())!=1){
            return $cartItem->sum(CartItem::raw('discountAmount * quantity'));
        }else{
            return $cartItem->first()['quantity']*$cartItem->first()['discountAmount'];
        }
    }

    public function calculateTotalQuantity()
    {
        $cartItem=CartItem::where('cartId',$this->cartId);
        if (sizeof($cartItem->get())!=1){
            return $cartItem->sum('quantity');
        }else{
            return $cartItem->first()['quantity'];
        }
    }

    public function getCartItemsBookId()
    {
        return CartItem::where('cartId',$this->cartId)
            ->pluck('bookId');
    }

    public function getCartItemsQuantity()
    {
        return CartItem::where('cartId',$this->cartId)
            ->pluck('quantity');
    }

    public function deleteCartItem()
    {
        CartItem::where([
            ['cartId', $this->cartId],
            ['bookId', $this->bookId]
        ])->delete();
    }

    public function deleteCartItems()
    {
        $cartItems=CartItem::where('cartId', $this->cartId);
        if ($cartItems->exists()){
            $cartItems->delete();
        }
    }

    public function checkCartEmpty()
    {
        if (!CartItem::where('cartId',$this->cartId)->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function deleteCart()
    {
        $cart=Cart::where('id',$this->cartId);
        if ($cart->exists()){
            $cart->delete();
        }
    }

    public function updateBookQuantity($state)
    {
        $quantity=$this->getCartItemQuantity();
        if ($state=='up'){
            //increase quantity
            return $this->increaseQuantity($quantity);
        }else{
            //decrease quantity
            return $this->decreaseQuantity($quantity);
        }
    }

    public function increaseQuantity($quantity)
    {
        $inventory=$this->getBookInventory();
        if ($inventory<$quantity+1){
            return false;
        }else{
            $this->increaseCartItemQuantity($quantity);
            return true;
        }
    }

    public function decreaseQuantity($quantity)
    {
        if ($quantity-1==0){
            $this->deleteCartItem();
            if ($this->checkCartEmpty()){
                $this->deleteCart();
                $this->deleteOrderWithCartEmpty();
            }
        }else{
            $this->updateCartItemQuantity($quantity-1);
        }
        return true;
    }

    public function increaseCartItemQuantity($quantity)
    {
        $this->updateCartItemQuantity($quantity+1);
    }

    public function updateCartItemQuantity($quantity)
    {
        CartItem::where([['cartId',$this->cartId],['bookId',$this->bookId]])
            ->update(['quantity'=>$quantity]);
    }

    public function deleteOrderWithCartEmpty()
    {
        $orderHelper=new OrderHelper();
        $orderHelper->cartId=$this->cartId;

        $orderHelper->deleteOrder();
    }

    public function updateCartItemQPD($quantity,$isAvailable=1)
    {
        $cartItem= CartItem::where([
            ['cartId',$this->cartId],
            ['bookId',$this->bookId]
        ]);

        $oldPrice=$cartItem->first()['price'];
        $newPrice=$this->getBookPrice();
        $oldDis=$cartItem->first()['discountAmount'];
        $newDis=$this->getBookDiscount();

        $this->setChangePriceMessages($oldPrice,$newPrice);

        $cartItem->update([
            'isAvailable'=>$isAvailable,
            'quantity'=> $quantity,
            'price'=>$newPrice,
            'discountAmount'=>$newDis,
            'isDaily'=>$this->isDaily
        ]);
    }

    public function getBookDiscount()
    {
        //check Daily discount
        $this->isDaily=0;
        $result=$this->getDailyDiscount();
        if ($result=='no daily discount'){
            //get normal discount
            $discountAmount=$this->getNormalDiscount();
        }else{
            $discountAmount=$result;
        }
        return $discountAmount;
    }

    public function getDailyDiscount()
    {
        $hasDailyDiscount= $this->bookRecord['isDailyDiscount'];
        $expDate=$this->bookRecord['dailyDiscountExpDate'];

        if ($hasDailyDiscount){
            //check exp date
            $dailyCount=$this->bookRecord['dailyCount'];
            if (!$this->checkDailyDiscountExpDateAndCountLimit($expDate,$dailyCount)){
                $this->isDaily=1;
                return $this->bookRecord['discountAmount'];
            }else{
                //has daily discount but expired
                return 0;
            }
        }else{
            return 'no daily discount';
        }
    }

    public function checkDailyDiscountExpDateAndCountLimit($expDate,$dailyCount)
    {
        $helper=new Libraries\Helper();

        $currentDate=$helper->getCurrentDate();
        if ($expDate<$currentDate || $dailyCount==0){
            return true;
        }else{
            return false;
        }
    }

    public function getNormalDiscount()
    {
        return $this->bookRecord['discountAmount'];
    }

    public function setChangeQuantityMessages($state)
    {
        $goodName=$this->getGoodName();
        switch ($state){
            case 'change':
                $message='تعداد انتخاب شده خاطر کافی نبودن موجودی به اندازه موجودی تغییر کرد';
                $orderMessage=" تعداد کتاب ".$goodName."به خاطر کم بودن موجودی به اندازه موجودی تغییر پیدا کرد";
                break;
            case 'withOutChange':
                $message="";
                $orderMessage="";
                break;
            case 'unavailable':
                $message='ناموجود شد';
                $orderMessage=" کتاب ".$goodName." ناموجود شد ";
                break;
            case 'available':
                $message='موجود شد';
                $orderMessage="کتاب  ".$goodName." موجود شد";
        }

        $messages=$this->quantityChangeMessages;
        array_push($messages,$message);
        $this->quantityChangeMessages=$messages;

        if ($orderMessage!=""){
            $orderMessages=$this->orderProcessMessages;
            array_push($orderMessages,$orderMessage);
            $this->orderProcessMessages=$orderMessages;
        }
    }

    public function setChangePriceMessages($oldPrice,$newPrice)
    {
        switch (true){
            case $oldPrice-$newPrice>0:
                $diff=$oldPrice-$newPrice;
                $message=" قیمت جدید نسبت به قیمت قبلی $diff کاهش یافت ";
                break;
            case $oldPrice-$newPrice<0:
                $diff=$newPrice-$oldPrice;
                $message="قیمت جدید نسبت به قبلی $diff افزایش یافت";
                break;
            case $oldPrice-$newPrice==0:
                $message="";
                break;
        }

        $messages=$this->priceChangeMessages;
        array_push($messages,$message);
        $this->priceChangeMessages=$messages;

    }

    public function getGoodName()
    {
        return Book::where('id',$this->bookId)->pluck('name')[0];
    }

    public function getCartQuantity()
    {
        return Cart::where('id',$this->cartId)->pluck('totalQuantity')[0];
    }

}
