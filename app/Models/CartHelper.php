<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartHelper extends Model
{
    //
    protected $fillable=[
        'userId','bookId','storeId'
    ];

    public function checkExistenceStore()
    {
        if(Store::where('id',$this->storeId)->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function checkExistenceBook()
    {
        if(Book::where('id',$this->bookId)->exists()){
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

    public function checkBookInCart()
    {
        $cartId=$this->getCartId();

        $cartItem=CartItem::where([
            ['cartId',$cartId],
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
        //$cartItem->price=$this->getBookPrice($bookId);
        // $cartItem->discountAmount=$this->getBookDiscount($bookId);
        $cartItem->weight=$this->getBookWeight();
        $cartItem->save();
    }

    public function updateCart()
    {
        $cartItem=new CartItem();

        $cartItem->cartId=$this->getCartId();
        $cartItem->bookId=$this->bookId;
        //$cartItem->price=$this->getBookPrice($bookId);
        // $cartItem->discountAmount=$this->getBookDiscount($bookId);
        $cartItem->weight=$this->getBookWeight();
        $cartItem->save();
    }

    public function createOrUpdateCart($resultStoreInCart)
    {
        if ($resultStoreInCart){
            $resultBookInCart=$this->checkBookInCart();
            if ($resultBookInCart){
                return response()->json(['status'=>'error','message' => 'this book has already been added to the cart'],409);
            }else{
                $this->updateCart();
                return response()->json(['message' => 'add book to cart successfully'],200);
            }
        }else{
            $this->createNewCart();
            return response()->json(['message' => 'add book to cart successfully'],200);
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
        return Book::where('id',$this->bookId)->pluck('inventory')[0];
    }
}
