<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Libraries;

class CartHelper extends Model
{
    //
    protected $fillable=[
        'userId','bookId','storeId','cartId'
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

    public function checkStoreInCart()
    {
        if(Cart::where([['storeId',$this->storeId],['userId',$this->userId]])->exists()){
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
        $this->cartId=$this->getCartId();
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

    public function createNewCart()
    {
        $cart=new Cart();
        $cartItem=new CartItem();

        $cart->userId=$this->userId;
        $cart->storeId=$this->storeId;
        $cart->save();

        $cartItem->cartId=$cart->id;
        $cartItem->bookId=$this->bookId;
        $cartItem->save();
    }

    public function updateCart()
    {
        $cartItem=new CartItem();

        $cartItem->cartId=$this->getCartId();
        $cartItem->bookId=$this->bookId;
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
                $this->updateOrderDate();
                return true;
            }
        }else{
            $this->createNewCart();
            $this->createOrderForCart();
            return true;
        }
    }

    public function getBookWeight()
    {
        return Book::where('id',$this->bookId)->pluck('weight')[0];
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

    public function getBookInventory()
    {
        return StoreBook::where([['bookId',$this->bookId],['storeId',$this->storeId]])->pluck('inventory')[0];
    }

    public function getBookPrice()
    {
        return  StoreBook::where([['bookId',$this->bookId],['storeId',$this->storeId]])->pluck('price')[0];
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
        $orderDate=$helper->getCurrentDate();
        $cartId=$this->getCartId();

        $order->where('id',$cartId)
            ->update(['orderDate'=>$orderDate]);
    }

    public function checkAndGetCartsData()
    {
        $userCartsId=$this->getUserCartsId();
        foreach ($userCartsId as $cartId){
            $this->cartId=$cartId;
            $this->checkAndUpdateCartItem();
            $this->updateCartQPD();
        }
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
        $cart=Cart::where('carts.id',$this->cartId)
            ->join('stores','stores.id','carts.storeId')
            ->Join('orders','orders.id','carts.id')
            ->select('carts.*','orders.orderDate','orders.trackingCode','stores.name')
            ->first();

        $cart['books']=$this->getCartItemData();

        foreach ($cart['books']->toArray() as $index=>$book){
            $this->bookId=$book['id'];
            $cart['books'][$index]['images']=$this->getBookImages();
        }

        return $cart;
    }

    public function getCartLists()
    {
       $carts=Cart::where('carts.userId',$this->userId)
           ->join('stores','stores.id','carts.storeId')
           ->Join('orders','orders.id','carts.id')
           ->select('carts.*','orders.orderDate','orders.trackingCode','stores.name')
           ->get();

       foreach ($carts->toArray() as $key=>$cart){
           $this->cartId=$cart['id'];
           $carts[$key]['books']=$this->getCartItemData();

           foreach ($carts[$key]['books']->toArray() as $index=>$book){
               $this->bookId=$book['id'];
               $carts[$key]['books'][$index]['images']=$this->getBookImages();
           }
       }

        return $carts;
    }

    public function getBookImages()
    {
        return BookImage::where('bookId',$this->bookId)->get();
    }

    public function getCartItemData()
    {
        return CartItem::where('cartId',$this->cartId )
            ->join('books','books.id','cartitems.bookId')
            ->select('cartitems.*','books.name','books.publisher','books.authors')
            ->get();
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
           $bookInventory=$this->getBookInventory();
           $bookQuantity=$cartItemsQuantity[$key];

           $flag=0;
           if ($bookInventory==0){
               //delete this book from cart item
               $this->deleteCartItem();
               $flag=1;
           }elseif($bookQuantity>$bookInventory){
               $quantity=$bookInventory;
           }else{
               $quantity=$bookQuantity;
           }

           if ($flag!=1){
               $this->updateCartItemQPD($quantity);
           }
       }

        $isCartEmpty=$this->checkCartEmpty();
        if ($isCartEmpty){
            $this->deleteCart();
            $this->deleteOrderWithCartEmpty();
        }
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

    public function calculateTotalPrice()
    {
        return CartItem::where('cartId',$this->cartId)
            ->sum(CartItem::raw('price * quantity'));
    }

    public function calculateTotalDiscount()
    {
        return CartItem::where('cartId',$this->cartId)
            ->sum(CartItem::raw('discountAmount * quantity'));
    }

    public function calculateTotalQuantity()
    {
        return CartItem::where('cartId',$this->cartId)
            ->sum('quantity');
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

    public function updateBookQuantity($count)
    {
        $bookInventory=$this->getBookInventory();

        if($bookInventory==0){
            //delete book from cart
            $this->deleteCartItem();
            return true;
        }elseIf($bookInventory<$count){
            return false;
        }else{
            $this->updateCartItemQPD($count);
            return true;
        }
    }

    public function deleteOrderWithCartEmpty()
    {
        $orderHelper=new OrderHelper();
        $orderHelper->cartId=$this->cartId;

        $orderHelper->deleteOrder();
    }

    public function updateCartItemQPD($quantity)
    {
        CartItem::where([
            ['cartId',$this->cartId],
            ['bookId',$this->bookId]
        ])->update([
            'quantity'=> $quantity,
            'price'=>$this->getBookPrice(),
            'discountAmount'=>$this->getBookDiscount()
        ]);
    }

    public function getBookDiscount()
    {
        //check Daily discount
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
        $book=StoreBook::where([['bookId',$this->bookId],['storeId',$this->storeId]]);
        $hasDailyDiscount=$book->pluck('isDailyDiscount')[0];
        $expDate=$book->pluck('dailyDiscountExpDate')[0];

        if ($hasDailyDiscount){
            //check exp date
            if (!$this->checkDailyDiscountExpDate($expDate)){
                return $book->pluck('discountAmount')[0];
            }else{
                //has daily discount but expired
                return 0;
            }
        }else{
            return 'no daily discount';
        }
    }

    public function checkDailyDiscountExpDate($expDate)
    {
        $helper=new Libraries\Helper();

        $currentDate=$helper->getCurrentDate();
        if ($expDate<$currentDate){
            return true;
        }else{
            return false;
        }
    }

    public function getNormalDiscount()
    {
        return StoreBook::where([['bookId',$this->bookId],['storeId',$this->storeId]])
            ->pluck('discountAmount')[0];
    }


}
