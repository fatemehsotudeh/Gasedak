<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    //
    public function verifyPayment(Request $request)
    {
        $payment=new Payment();
        return $payment->verifyPayment($request);
    }
}
