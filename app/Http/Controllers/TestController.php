<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\CartItem;
use App\Models\Home;
use App\Models\StoreAddress;
use App\Models\StoreBook;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Libraries;


class TestController extends Controller
{
    //
    public function test(Request $request)
    {
       return Home::getSpecialPublicationBooks();
    }

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
