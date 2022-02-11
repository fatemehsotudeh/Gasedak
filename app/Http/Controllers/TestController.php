<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\CartItem;
use App\Models\Home;
use App\Models\Order;
use App\Models\SMSToken;
use App\Models\StoreAddress;
use App\Models\StoreBook;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Libraries;


class TestController extends Controller
{
    //
    public function test(Request $request)
    {
        $SMS=new SMSToken();

        $order=new Order();
        $order->id=19;
        $SMS->message=$order->getSMSMessageToStore();
        $SMS->phoneNumber="09302258594";
        $SMS->sendSMS();
        //return $order->getSMSMessageToStore();
        //return DB::table('users')->where('id',31)->get();
//        $array1=[
//            [
//                'price' => 130000,
//                'discountAmount' => 1000,
//            ],
//            [
//                'price' => 230000,
//                'discountAmount' => 30000,
//            ],
//            [
//                'price' => 34000,
//                'discountAmount' => 12,
//            ]
//        ];

//        $array = collect($array1)->sortBy('discountAmount')->toArray();
//        return array_values($array);


//        $array = array( '1' => 3, '8' => 0 ,'3' =>0);
//        $suffle=$this->shuffleAssociativeArray($array);
//        $min = min($suffle);
//        $index = array_search($min, $suffle);
        //echo $index;
//        $books = Book::all();
//
//        foreach ($books as $book) {
//            $storebooks=new StoreBook();
//            $storebooks->bookId=$book['id'];
//
//            $price=[];$discountAmount=[];$isDaily=[];$expDate=[];$dailyCount=[];
//            $storesWithThisBook=$storebooks->getAllStoresWithThisBook();
//
//            foreach ($storesWithThisBook as $storebook){
//                $price[$storebook['storeId']]=$storebook['price'];
//                $discountAmount[$storebook['storeId']]=$storebook['discountAmount'];
//                $isDaily[$storebook['storeId']]=$storebook['isDailyDiscount'];
//                $dailyCount[$storebook['storeId']]=$storebook['dailyCount'];
//                $expDate[$storebook['storeId']]=$storebook['dailyDiscountExpDate'];
//            }
//
//            $discountsAfterCheck=$this->checkAndGetDiscount($discountAmount,$isDaily,$expDate,$dailyCount);
//            $priceAfterDiscount=$this->getPriceAfterDiscount($price,$discountsAfterCheck);
//            $shufflePriceAfterDiscount=$this->shuffleAssociativeArray($priceAfterDiscount);
//            $storeId=$this->getStoreIdWithMinimumPrices($shufflePriceAfterDiscount);
//            $book['storeId']=$storeId;
//            $book['price']=$price[$storeId];
//            $book['discountAmount']=$discountsAfterCheck[$storeId];
//            $book['isDaily']=$isDaily[$storeId];
//        }
//
//        return $books;


//        //$stores=StoreAddress::all();
//        $listLat2=StoreAddress::all()->pluck('lat','id');
//        $listLng2=StoreAddress::all()->pluck('lng','id');
//
//        return $listLat2;
//
//        $keyWord=$request->keyWord;
//        $words=explode(" ",$keyWord);
//        $books=Book::all();
//
//       // return $words;
//        $checkResult=[];
//
//        $findBooks=[];
//
//        foreach ($books as $book){
//            $check=[];
//            foreach ($words as $index => $word){
//                $check[0]=$this->searchInBookName($book['name'],$word);
//                $check[1]=$this->searchInBookName($book['publisher'],$word);
//                $check[2]=$this->searchInAuthors($book['authors'],$word);
//                $check[3]=$this->searchInTranslators($book['translators'],$word);
//            }
//            if (in_array("true", $check)){
//                array_push($findBooks,$book);
//            }
//        }
//
//        return $findBooks;

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


//snap api

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


        $data = [
            "itemDetails" => [
                [
                    "pickedUpSequenceNumber" => 1,
                    "dropOffSequenceNumber" => 2,
                    "name" => "کره پاستوریزه ۵۰ گرمی پگاه",
                    "quantity" => 3,
                    "quantityMeasuringUnit" => "unit",
                    "packageValue" => 5589,
                    "createdAt" => "2019-02-26 18:08:03",
                    "updatedAt" => "2019-02-26 18:08:03"
                ],
                [
                    "pickedUpSequenceNumber" => 1,
                    "dropOffSequenceNumber" => 2,
                    "name" => "خامه صبحانه 125 میلی لیتری کاله",
                    "quantity" => 2,
                    "quantityMeasuringUnit" => "unit",
                    "packageValue" => 4800,
                    "createdAt" => "2019-02-26 18:08:03",
                    "updatedAt" => "2019-02-26 18:08:03"
                ]],
            "orderDetails" => [
                "packageSize" => 0.2,
                "city" => "tehran",
                "deliveryCategory" => "bike",
                "deliveryFarePaymentType" => "cod",
                "isReturn" => false,
                "pricingId" => "8e941a24-f17c-47d3-bae5-299f3113a240",
                "sequenceNumberDeliveryCollection" => 1,
                "totalFare" => 105000,
                "customerRefId" => 31,
                "voucherCode" => null,
                "waitingTime" => 0
            ],
            "pickUpDetails" => [
                "id" => null,
                "contactName" => "مارکت سعادت",
                "address" => "جردن ",
                "contactPhoneNumber" => "09376764602",
                "plate" => "",
                "sequenceNumber" => 1,
                "unit" => "",
                "comment" => "",
                "latitude" => 35.784869,
                "longitude" => 51.376754,
                "type" => "pickup",
                "collectCash" => "no",
                "paymentType" => "prepaid",
                "cashOnPickup" => 0,
                "cashOnDelivery" => 0,
                "isHub" => null,
                "vendorId" => null
            ],
            "dropOffDetails" => [
                "id" => null,
                "contactName" => "نام تستی کانتکت",
                "address" => "maghsad2 ",
                "contactPhoneNumber" => "09108986973",
                "plate" => "",
                "sequenceNumber" => 2,
                "unit" => "",
                "comment" => "کامنت تستی",
                "latitude" => 35.706674,
                "longitude" => 51.364912,
                "type" => "drop",
                "collectCash" => "no",
                "paymentType" => "prepaid",
                "cashOnPickup" => 0,
                "cashOnDelivery" => 0,
                "isHub" => null,
                "vendorId" => null
            ],
        ];

//        $jsonData = json_encode($data);
//        $ch = curl_init('https://customer-stg.snapp-box.com/v1/customer/create_order');
//        curl_setopt($ch, CURLOPT_USERAGENT, 'Snapp Rest Api v1');
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            'Content-Type: application/json',
//            // 'Content-Length: ' . strlen($jsonData),
//            'Authorization:eyJhbGciOiJIUzUxMiJ9.eyJjaWQiOjExODg1NzU4LCJjcmlkIjoiMjEwMjg5NzMiLCJlIjoic2luYWJhcmF6YW5kZWgwMUBnbWFpbC5jb20iLCJ3ZSI6dHJ1ZSwic3ViIjoiMDkzNzE4NjMwOTQiLCJhdXRoIjoiUk9MRV9DVVNUT01FUiIsInR5cGUiOiJjdXN0b21lciJ9.FjKT5p2POrit_dSVJ8j-TJt0XgIPdsRsZ6CahDIDvjL6f1uyI4x4VuxMnd8hDErxYpq21wGR3NUGs7IFAHE_dw'
//        ));
////
//        $result = curl_exec($ch);
//        $err = curl_error($ch);
//        $result = json_decode($result, true, JSON_PRETTY_PRINT);
//        curl_close($ch);
//
//
//        return $result;

    }
//    public function searchInBookName($name,$word)
//    {
//        if (strpos($name,$word)!==false){
//            return true;
//        }else{
//            return false;
//        }
//    }
//
//    public function searchInAuthors($authors,$word)
//    {
//        if (sizeof($authors)==0){
//            return false;
//        }
//
//
//        $flag=0;
//        foreach ($authors as $author){
//            if (strpos($author,$word)!==false){
//                $flag=1;
//            }
//        }
//
//        if ($flag==1){
//            return true;
//        }else{
//            return false;
//        }
//    }

//    public function searchInTranslators($translators,$word)
//    {
//        if (is_array($translators)){
//            if (sizeof($translators)==0){
//                return false;
//            }
//        }else{
//            return false;
//        }
//
//        $flag=0;
//        foreach ($translators as $translator){
//            if (strpos($translator,$word)!==false){
//                $flag=1;
//            }
//        }
//
//        if ($flag==1){
//            return true;
//        }else{
//            return false;
//        }
//    }

//    public function shuffleAssociativeArray($array)
//    {
//        $sorted_array = $array;
//        $shuffled_array = array();
//        $keys = array_keys($sorted_array);
//        shuffle($keys);
//
//        foreach ($keys as $key)
//        {
//            $shuffled_array[$key] = $sorted_array[$key];
//        }
//
//        return $shuffled_array;
//    }

//    public function checkAndGetDiscount($discountAmounts,$isDailies,$expDates,$dailyCounts)
//    {
//        $discountsAfterCheck=[];
//       foreach ($isDailies as $storeId => $isDaily){
//          if ($isDaily){
//              if ($this->checkDailyDiscountNotExpired($expDates[$storeId],$dailyCounts[$storeId])){
//                  $discountsAfterCheck[$storeId]=$discountAmounts[$storeId];
//              }else{
//                  $discountsAfterCheck[$storeId]=0;
//              }
//          }else{
//              $discountsAfterCheck[$storeId]=$discountAmounts[$storeId];
//          }
//       }
//       return $discountsAfterCheck;
//    }
//
//    public function checkDailyDiscountNotExpired($expDate,$dailyCount)
//    {
//        $helper=new Libraries\Helper();
//
//        $currentDate=$helper->getCurrentDate();
//        if ($expDate<$currentDate || $dailyCount==0){
//            return false;
//        }else{
//            return true;
//        }
//    }
//
//    public function getPriceAfterDiscount($prices,$discounts)
//    {
//        $discountedPrice=[];
//        foreach ($prices as $storeId => $price){
//            $discountedPrice[$storeId]=$price-$discounts[$storeId];
//        }
//        return $discountedPrice;
//    }
//
//    public function getStoreIdWithMinimumPrices($array)
//    {
//         $min = min($array);
//         return array_search($min, $array);
//    }

//    public function orderBy($array, $sortOrder)
//    {
//        usort($array, function ($a, $b) use ($sortOrder) {
//            $result = '';
//
//            $sortOrderArray = explode(',', $sortOrder);
//            foreach ($sortOrderArray AS $item) {
//                $itemArray = explode(' ', trim($item));
//                $field = $itemArray[0];
//                $sort = !empty($itemArray[1]) ? $itemArray[1] : '';
//
//                $mix = [$a, $b];
//                if (!isset($mix[0][$field]) || !isset($mix[1][$field])) {
//                    continue;
//                }
//
//                if (strtolower($sort) === 'desc') {
//                    $mix = array_reverse($mix);
//                }
//
//                if (is_numeric($mix[0][$field]) && is_numeric($mix[1][$field])) {
//                    $result .= ceil($mix[0][$field] - $mix[1][$field]);
//                } else {
//                    $result .= strcasecmp($mix[0][$field], $mix[1][$field]);
//                }
//            }
//
//        });
//
//        return $array;
//    }

}
