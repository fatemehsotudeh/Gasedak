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

    public function getStoreAllBooksPaginated()
    {
        return StoreBook::where('storeId',$this->storeId)
            ->join('books','books.id','storebooks.bookId')
            ->paginate(10);
    }

    public function getBookData()
    {
        return Book::where('id',$this->bookId)->first()->toArray();
    }

    public function getStoresIdWithThisBook()
    {
        return StoreBook::where('bookId',$this->bookId)->pluck('storeId');
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

    public function getSpecificStoreDataBasedNearest($distances)
    {
        $stores=[];
        foreach ($distances as $storeId=>$distance){
            $stores[]=Store::where('stores.id',$storeId)
                ->join('storesaddress','storesaddress.storeId','stores.id')
                ->join('storebooks','storebooks.storeId','stores.id')
                ->select('storesaddress.*','storebooks.*','stores.*')
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
        $helper=new Libraries\Helper();
        $collectionData=collect($data);
        return $helper->paginate($request,$collectionData);
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

        foreach ($books as $book){
            $check=[];
            foreach ($words as $index => $word){
                $check[0]=$this->searchInBookNameOrPublisher($book['name'],$word);
                $check[1]=$this->searchInBookNameOrPublisher($book['publisher'],$word);
                $check[2]=$this->searchInAuthorsOrTranslatores($book['authors'],$word);
                $check[3]=$this->searchInAuthorsOrTranslatores($book['translators'],$word);
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

    public function searchInBookNameOrPublisher($name,$word)
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
                ->join('storebooks','storebooks.bookId','books.id')
                ->select('storebooks.*','books.*','storebooks.created_at','storebooks.updated_at');
    }

    public function getOrderByResults($data,$orderBy)
    {
        switch ($orderBy){
            case 'جدید ترین':
                return $data->orderBy('created_at','DESC')->get();
                break;
            case 'پرفروش ترین':
                return $data->orderBy('purchaseCount','DESC')->get();
                break;
            case 'گران ترین':
                return $data->orderByRaw('(price - discountAmount) DESC')->get();
                break;
            case 'ارزان ترین':
                return $data->orderByRaw('(price - discountAmount)')->get();
                break;
            default:
                return $data->get();
        }
    }

}
