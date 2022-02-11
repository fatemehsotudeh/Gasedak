<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    //
    protected $table='wallets';
    protected $fillable = [
        'userId','balance','bankId'
    ];

    public function checkWalletExists()
    {
        if (Wallet::where('userId',$this->userId)->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function updateBalance($orderCost)
    {
        $wallet=Wallet::where('userId',$this->userId);
        $balance=$wallet->pluck('balance')[0];
        $wallet->update([
           'balance' => $balance+$orderCost
        ]);
    }

    public function createAndUpdateWallet()
    {
        $this->save();
    }
}
