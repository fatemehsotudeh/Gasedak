<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Libraries;

class StoreBook extends Model
{
    //
    protected $table='storebooks';
    protected $fillable=['bookId','bookData','storeData','categoryId'];

    protected $casts=[
        'translators'=> 'array',
        'authors'=>'array'
    ];

    protected $hidden = [
        'password','email','username','IBAN'
    ];

    public function checkExistenceBookInStore()
    {
        if (StoreBook::where('bookId',$this->bookId)->exists()){
            $this->bookData=$this->getBookData();
            return true;
        }else{
            return false;
        }
    }

    public function checkStoreOpen()
    {
        $this->storeData=Store::where('id',$this->storeId)->first();
        if ($this->storeData['isOpen']==1){
            return true;
        }else{
            return false;
        }
    }

    public function checkStoreNotSuspended()
    {
        if ($this->storeData['isSuspended']==0){
            return true;
        }else{
            return false;
        }
    }

    public function checkStoreNotSuspendedV2()
    {
        if (Store::where('id',$this->storeId)->pluck('isSuspended')[0]!=1){
            return true;
        }else{
            return false;
        }
    }

    public function checkStoreHasBooks()
    {
        if (StoreBook::where('storeId',$this->storeId)->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function getStoreAllBooks()
    {
        return StoreBook::where('storeId',$this->storeId)
            ->join('books','books.id','storebooks.bookId')
            ->get();
    }

    public function getAllStores()
    {
        return Store::all();
    }

    public function getAllBooks()
    {
        return Book::all();
    }

    public static function getBooks()
    {
        return Book::all();
    }

    public function getAllStoresWithThisBook()
    {
        return StoreBook::where('bookId',$this->bookId)->get();
    }

    public function getStoreAllBooksPaginated()
    {
        return StoreBook::where('storeId',$this->storeId)
            ->join('books','books.id','storebooks.bookId')
            ->paginate(10);
    }

    public function getBookData()
    {
        $this->bookData=Book::where('id',$this->bookId)->first()->toArray();
        return $this->bookData;
    }

    public function getBookHashtagsAndConvertToArray()
    {
        $hashtags=explode('#',$this->bookData['hashtags']);
        $hashtagsAfterConvert=[];
        foreach ($hashtags as $key=>$hashtag){
            if ($hashtag!=""){
                array_push($hashtagsAfterConvert,'#'.$hashtag);
            }
        }
        return $hashtagsAfterConvert;
    }

    public function getStoreHashtagsAndConvertToArray($storeData)
    {
        $hashtags=explode('#',$storeData['hashtags']);
        $hashtagsAfterConvert=[];
        foreach ($hashtags as $key=>$hashtag){
            if ($hashtag!=""){
                array_push($hashtagsAfterConvert,'#'.$hashtag);
            }
        }
        return $hashtagsAfterConvert;
    }

    public function getStoresIdWithThisBook()
    {
        return StoreBook::where([['bookId',$this->bookId],['inventory','!=',0]])->pluck('storeId');
    }

    public function getStoresLatAndLng($storeIds=null,$keyWord=null)
    {
        $lats=[];
        $lngs=[];
         if ($storeIds!=null){
             foreach ($storeIds as $storeId){
                 $store=StoreAddress::where('storeId',$storeId);
                 $lat=$store->pluck('lat')[0];
                 $lats[$storeId]=$lat;
                 $lng=$store->pluck('lng')[0];
                 $lngs[$storeId]=$lng;
             }
         }elseif($keyWord==null){
             $stores=StoreAddress::all();
             $lats=$stores->pluck('lat','id');
             $lngs=$stores->pluck('lng','id');
         }else{
             $stores=StoreAddress::join('stores','stores.id','storesaddress.storeId')->where('stores.name','like','%'.$keyWord.'%');
             $lats=$stores->pluck('lat','storesaddress.id');
             $lngs=$stores->pluck('lng','storesaddress.id');
         }
        return ['lats'=>$lats,'lngs'=>$lngs];
    }

    public function getUserDistanceToBookStores($userLat,$userLng,$storesLatAndLng)
    {
        $helper=new Libraries\Helper();
        $storesLat=$storesLatAndLng['lats'];
        $storesLng=$storesLatAndLng['lngs'];

        $distances=[];
        foreach ($storesLng as $key=>$value){
            $distance=$helper->distance(floatval($userLat),floatval($userLng),floatval($storesLat[$key]),floatval($storesLng[$key]),'k');
            $distances[$key]=$distance;
        }

        asort($distances);
        return $distances;
    }

    public function getSpecificStoreDataBasedNearest($distances,$bookId)
    {
        $stores=[];
        foreach ($distances as $storeId=>$distance){
            $stores[]=StoreBook::where([['storebooks.storeid',$storeId],['storebooks.bookId',$bookId]])
                ->join('storesaddress','storesaddress.storeId','storebooks.storeId')
                ->join('stores','stores.id','storesaddress.storeId')
                ->first();
        }
        return $stores;
    }

    public function getAllStoreDataBasedNearest($request,$distancesAndIds)
    {
        $stores=[];
        foreach ($distancesAndIds as $id=>$distance){
            $stores[]=StoreAddress::where('storesaddress.id',$id)
                ->join('stores','stores.id','storesaddress.storeId')
                ->first();
        }
        if (sizeof($stores)!=0){
            return $this->paginateData($request,$stores);
        }else{
            return [];
        }
    }

    public function paginateData($request,$data)
    {
        if ($data!=[]){
            $helper=new Libraries\Helper();
            $collectionData=collect($data);
            return $helper->paginate($request,$collectionData);
        }else{
            return [];
        }

    }

    public function getBookComments($request)
    {
        $helper=new Libraries\Helper();

        $comments=Comment::where([['bookId',$this->bookId],['isApproved',1]]);
        if ($comments->exists()){
            $userComments = $comments->select('comments.*','users.firstname','users.lastname')
                ->join('users','users.id','comments.userId')
                ->get();
            return $helper->paginate($request,$userComments);
        }else{
            return [];
        }
    }

    public function getBookCommentCount()
    {
        return Comment::where([['bookId',$this->bookId],['isApproved',1]])->count();
    }

    public function updateBookCountInfo()
    {
        Book::where('id',$this->bookId)
            ->update([
                'viewCount'=> $this->increaseAndGetBookViewCount(),
                'commentCount'=> $this->getBookCommentCount(),
                'rate'=> $this->calculateAndGetBookRate()
            ]);
    }

    public function increaseAndGetBookViewCount()
    {
        $viewCount=$this->bookData['viewCount'];
        $newViewCount=$viewCount+1;
        return $newViewCount;
    }

    public function calculateAndGetBookRate()
    {
        $comment=Comment::where([['bookId',$this->bookId],['rate','!=',null]]);
        if ($comment->exists()){
            return $comment->average('rate');
        }else{
            return 0;
        }
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

    public function advanceSearchInBooks($books,$keyWord)
    {
        $words=explode(" ",$keyWord);
        $findBooks=[];

        foreach ($books as $key=>$book){
            $check=[];
            foreach ($words as $index => $word){
                $check[0]=$this->searchInBookNameOrPublisherOrOriginality($book['name'],$word);
                $check[1]=$this->searchInBookNameOrPublisherOrOriginality($book['publisher'],$word);
                $check[2]=$this->searchInBookNameOrPublisherOrOriginality($book['originality'],$word);
                $check[3]=$this->searchInAuthorsOrTranslatores($book['authors'],$word);
                $check[4]=$this->searchInAuthorsOrTranslatores($book['translators'],$word);
                $check[5]=$this->searchInISBN($book['ISBN'],$word);
            }
            if ($check[5]==true){
                array_push($findBooks,$book);
                break;
            }

            if (in_array(true, $check)){
                array_push($findBooks,$book);
            }
        }

        return $findBooks;
    }

    public function advanceSearchInStores($stores,$keyWord)
    {
        $words=explode(" ",$keyWord);
        $findStores=[];

        foreach ($stores as $store){
            $check=[];
            $index=0;
            foreach ($words as $index => $word){
                $check[$index++]=$this->searchInStores($store['name'],$word);
            }
            if (!in_array(false, $check)){
                array_push($findStores,$store);
            }
        }

        return $findStores;
    }

    public function searchInStores($name,$word)
    {
        if (strpos($name,$word)!==false){
            return true;
        }else{
            return false;
        }
    }

    public function searchInBookNameOrPublisherOrOriginality($name,$word)
    {
        if (strpos($name,$word)!==false){
            return true;
        }else{
            return false;
        }
    }

    public function searchInAuthorsOrTranslatores($authorsOrTranslators,$word)
    {
        if (is_array($authorsOrTranslators)){
            if (sizeof($authorsOrTranslators)==0){
                return false;
            }
        }else{
            return false;
        }

        $flag=0;
        foreach ($authorsOrTranslators as $authorOrTranslator){
            if (strpos($authorOrTranslator,$word)!==false){
                $flag=1;
            }
        }

        if ($flag==1){
            return true;
        }else{
            return false;
        }
    }

    public function searchInISBN($ISBN,$word)
    {
        if (strpos($ISBN,$word)!==false){
            return true;
        }else{
            return false;
        }
    }

    public function getAllCategories()
    {
        return Category::all();
    }

    public function checkExistenceCategory($categories,$userCategory)
    {
        $flag=0;
        foreach ($categories as $category){
            if ($category['title']==$userCategory){
               $flag=1;
               $this->categoryId=$category['id'];
               break;
            }
        }

        if ($flag==1){
            return true;
        }else{
            return false;
        }

    }

    public function getBooksRelatedCategory()
    {
        return Book::where('categoryId',$this->categoryId)
                ->get();
    }

    public function getOrderByResults($array,$orderBy)
    {
        $collectArray = collect($array);
        $orderArray=[];

        switch ($orderBy){
            case 'جدید ترین':
                $orderArray = $collectArray->sortBy('created_at')->reverse()->toArray();
                break;
            case 'پرفروش ترین':
                $orderArray = $collectArray->sortBy('purchaseCount')->reverse()->toArray();
                break;
            case 'گران ترین':
                $orderArray = $collectArray->sortBy(function($array) {
                    return $array['price']-$array['discountAmount'];
                })->reverse()->toArray();
                break;
            case 'ارزان ترین':
                $orderArray = $array->sortBy(function($array) {
                    return $array['price']-$array['discountAmount'];
                })->toArray();
                break;
        }

        return array_values($orderArray);
    }

    public function addMinimumPriceAndDiscountToBooksFromStores($books)
    {
        $helper=new Libraries\Helper();

        foreach ($books as $key=>$book) {
            $storebooks=new StoreBook();
            $storebooks->bookId=$book['id'];

            $price=[];$discountAmount=[];$percentDiscountAmount=[];$isDaily=[];$expDate=[];$dailyCount=[];
            $storesWithThisBook=$storebooks->getAllStoresWithThisBook();

            foreach ($storesWithThisBook as $storebook){
                $this->storeId=$storebook['storeId'];
                if($this->checkStoreNotSuspendedV2()){
                    $price[$storebook['storeId']]=$storebook['price'];
                    $discountAmount[$storebook['storeId']]=$storebook['discountAmount'];
                    $percentDiscountAmount[$storebook['storeId']]=$storebook['percentDiscountAmount'];
                    $isDaily[$storebook['storeId']]=$storebook['isDailyDiscount'];
                    $dailyCount[$storebook['storeId']]=$storebook['dailyCount'];
                    $expDate[$storebook['storeId']]=$storebook['dailyDiscountExpDate'];
                }
            }

            if (sizeof($price)>0){
                $discountsAfterCheck=$this->checkAndGetDiscount($discountAmount,$isDaily,$expDate,$dailyCount);
                $percentDiscount=$this->checkAndGetDiscount($percentDiscountAmount,$isDaily,$expDate,$dailyCount);
                $priceAfterDiscount=$this->getPriceAfterDiscount($price,$discountsAfterCheck);
                $shufflePriceAfterDiscount=$helper->shuffleAssociativeArray($priceAfterDiscount);
                $storeId=$this->getStoreIdWithMinimumPrices($shufflePriceAfterDiscount);
                $book['storeId']=$storeId;
                $book['price']=$price[$storeId];
                $book['discountAmount']=$discountsAfterCheck[$storeId];
                $book['percentDiscountAmount']=$percentDiscount[$storeId];
                $book['isDaily']=$isDaily[$storeId];
                $this->bookId=$book['id'];
                $book['image']=$this->getBookImage();
            }else{
                unset($books[$key]);
            }
        }

        return $books;
    }

    public function checkAndGetDiscount($discountAmounts,$isDailies,$expDates,$dailyCounts)
    {
        $discountsAfterCheck=[];
        foreach ($isDailies as $storeId => $isDaily){
            if ($isDaily){
                if ($this->checkDailyDiscountNotExpired($expDates[$storeId],$dailyCounts[$storeId])){
                    $discountsAfterCheck[$storeId]=$discountAmounts[$storeId];
                }else{
                    $discountsAfterCheck[$storeId]=0;
                }
            }else{
                $discountsAfterCheck[$storeId]=$discountAmounts[$storeId];
            }
        }
        return $discountsAfterCheck;
    }

    public function checkDailyDiscountNotExpired($expDate,$dailyCount)
    {
        $helper=new Libraries\Helper();

        $currentDate=$helper->getCurrentDate();
        if ($expDate<$currentDate || $dailyCount==0){
            return false;
        }else{
            return true;
        }
    }

    public function getPriceAfterDiscount($prices,$discounts)
    {
        $discountedPrice=[];
        foreach ($prices as $storeId => $price){
            $discountedPrice[$storeId]=$price-$discounts[$storeId];
        }
        return $discountedPrice;
    }

    public function getStoreIdWithMinimumPrices($array)
    {
        $min = min($array);
        return array_search($min, $array);
    }

    public function addImageToBooks($books,$id='bookId')
    {
        foreach ($books as $book){
            $this->bookId=$book[$id];
            $book['image']=$this->getBookImage();
        }
    }

    public function checkBookDiscountsAndAddImage($books)
    {
        foreach ($books as $book){
            if ($book['isDailyDiscount']){
                if ($this->checkDailyDiscountNotExpired($book['dailyDiscountExpDate'],$book['dailyCount'])){
                    $discount=$book['discountAmount'];
                }else{
                    $discount=0;
                }
            }else{
                $discount=$book['discountAmount'];
            }
            $book['discountAmount']=$discount;
            $this->bookId=$book['id'];
            $book['image']=$this->getBookImage();
        }
        return $books;
    }

    public function getAllBooksWithIsDailyTrueAndNotExpired()
    {
        $helper=new Libraries\Helper();
        $currentDate=$helper->getCurrentDate();

        $books=StoreBook::where([
            ['isDailyDiscount',true],
            ['dailyCount','!=',0],
            ['dailyDiscountExpDate','>=',$currentDate]
        ])->join('books','books.id','storebooks.bookId')->get();

        $this->addImageToBooks($books);
        return $books;
    }

    public function orderByMostDiscount($books)
    {
        $collectBook = collect($books);

         $orderedBook=$collectBook->sortBy(function ($books, $key) {
                 return $books['percentDiscountAmount'].$books['created_at'];
                })->take(10)->toArray();

         return array_values($orderedBook);
    }

    public function checkDiscountsAndSuspended($stores)
    {
        foreach ($stores as $key=>$store){
            if ($store['isSuspended']!=1){
                if ($store['isDailyDiscount']){
                    if ($this->checkDailyDiscountNotExpired($store['dailyDiscountExpDate'],$store['dailyCount'],)){
                        $discount=$store['discountAmount'];
                    }else{
                        $discount=0;
                    }
                }else{
                    $discount=$store['discountAmount'];
                }
                $store['discountAmount']=$discount;
            }else{
                unset($stores[$key]);
            }
        }
        return $stores;
    }

    //    public function getOrderByResults($data,$orderBy)
//    {
//        switch ($orderBy){
//            case 'جدید ترین':
//                return $data->orderBy('created_at','DESC')->get();
//                break;
//            case 'پرفروش ترین':
//                return $data->orderBy('purchaseCount','DESC')->get();
//                break;
//            case 'گران ترین':
//                return $data->orderByRaw('(price - discountAmount) DESC')->get();
//                break;
//            case 'ارزان ترین':
//                return $data->orderByRaw('(price - discountAmount)')->get();
//                break;
//            default:
//                return $data->get();
//        }
//    }

}
