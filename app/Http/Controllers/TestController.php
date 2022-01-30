<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\CartItem;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Libraries;


class TestController extends Controller
{
    //
    public function test(Request $request)
    {
        $amount=12000;
       dd($amount."0");

//        $oldPrice=12000;
//        $newPrice=12000;
//
//        switch (true){
//            case $oldPrice-$newPrice>0:
//                return 'old bozorgeh';
//            case $oldPrice-$newPrice<0:
//                return 'new bozorgeh';
//            case $oldPrice-$newPrice==0:
//                return 'barabaran';
//
//        }

//       $cartItem=CartItem::where('cartId',14);
//        return $cartItem->first()['quantity'];

//        $amount=12000;
//        return ' قیمت محصول '.$amount.'  تومان کاهش یافت  ';
//        return Book::paginate(10);
//        $translator=$request->translators;
//        $authors=$request->authors;
//        $book=new Book();
//        $book->name="ملت عشق";
//        $book->inventory=12;
//        $book->price=12000;
//        $book->ISBN=45794534;
//        $book->translators=["الیف شافاک"];
//        $book->authors=["ارسلان فصیحی"];
       // $book->save();
//          return response()->json(Book::where('id',1)->pluck('translators')[0]);
//        $list=UserAddress::where('postalAddress','LIKE','%پلاک2%')->get();
//        return $list;
//        $helper=new Libraries\Helper();
//        $randomCode=$helper->generateRandomDigitsCode(5);

//        echo $randomCode;
//        $this->validate($request, [
//            'name' => 'required',
//            'email' => 'required|email|unique:users',
//            'phoneNumber'='regex:/^[0-9]{11}$/i'
//        ]);

        //upload file test
//        if ($request->has('image')){
//            return $request->file('image')."\n";

//            if ($request->file('image')->getSize()<= (2* pow(10,6))){
//                $imagePath=$request->file('image');
//                    ."    ".$request->file('image')->getMimeType();
//               return $request->file('image')->getClientOriginalName();
//                move_uploaded_file($imagePath,'../public/uploads/'.$request->file('image')->getClientOriginalName());
//                return $request->file('image')->getMimeType();
//                if ($request->file('image')->getMimeType()==="image/jpeg"){
//                    return 'oke';
//                }
//                return pathinfo($request->file('image')->getClientOriginalName());
//            }
//        }
//        $data = [
//            "city"=>"tehran",
//            "deliveryCategory"=> "bike-without-box",
//            "deliveryFarePaymentType"=> "prepaid",
//            "isReturn"=> false,
//            "pricingId"=> null,
//            "sequenceNumberDeliveryCollection"=> 1,
//            "totalFare"=> null,
//            "voucherCode"=> null,
//            "waitingTime"=> 0,
//            "customerWalletType"=> "SNAPP_BOX",
//            "terminals"=>[[    "id"=> null,
//            "contactName"=> "Kiana Ahmadi",
//            "address"=> "تهران، محله شیخ هادی (انقلاب -فلسطين)، حافظ",
//            "contactPhoneNumber"=> "09195428658",
//             "plate"=> "",
//             "sequenceNumber"=> 1,
//             "unit"=> "",
//             "comment"=> "",
//              "latitude"=> 35.69808314000005,
//                "longitude"=> 51.411552429199226,
//             "type"=> "pickup",
//             "collectCash"=> "no",
//             "paymentType"=> "prepaid",
//             "cashOnPickup"=> 0,
//             "cashOnDelivery"=> 0,
//              "isHub"=> null,
//             "vendorId"=> null
//            ],
//                [
//                    "id"=> null,
//    "contactName"=> "",
//    "address"=> "تهران، محله شیخ هادی (انقلاب -فلسطين)، حافظ",
//    "contactPhoneNumber"=> "",
//    "plate"=> "",
//    "sequenceNumber"=> 2,
//    "unit"=> "",
//    "comment"=> "",
//    "latitude"=> 35.69808314000005,
//    "longitude"=> 51.412582397460945,
//    "type"=> "drop",
//    "collectCash"=> "no",
//    "paymentType"=> "prepaid",
//    "cashOnPickup"=> 0,
//    "cashOnDelivery"=> 0,
//    "isHub"=> null,
//    "vendorId"=> null
//
//                ]
//            ],
//            "id"=> null
//        ];
//
//        $jsonData = json_encode($data);
//        $ch = curl_init('https://customer.snapp-box.com/v1/customer/order/pricing');
//        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            'Content-Type: application/json',
//           // 'Content-Length: ' . strlen($jsonData),
//            'Authorization:eyJhbGciOiJIUzUxMiJ9.eyJjaWQiOjExODg1NzU4LCJjcmlkIjoiMjEwMjg5NzMiLCJlIjoiIiwid2UiOmZhbHNlLCJzdWIiOiIwOTM3MTg2MzA5NCIsImF1dGgiOiJST0xFX0NVU1RPTUVSIiwidHlwZSI6ImN1c3RvbWVyIn0.WCxYOdktD4ByM1VlCwq4xUEDIbNWr2uW6lPPWqQ-l3nldyDbF2r_WbnUT0yMMQnlNIBDO8RpUtZBiK3IV3QFaw'
//        ));
//
//        $result = curl_exec($ch);
//        $err = curl_error($ch);
//        $result = json_decode($result, true, JSON_PRETTY_PRINT);
//        curl_close($ch);
//
//        return $result;

    }
}
