<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Libraries;

class Home extends Model
{
    //
    public static function getBanners()
    {
        return Banner::orderBy('created_at','DESC')
            ->take(10)
            ->get();
    }

    public static function getUserExclusiveOffer($userId)
    {
        $helper=new Libraries\Helper();
        $userFavorite=new UserFavoriteData();

        $userFavorite->userId=$userId;
        $userFavoriteData=$userFavorite->getUserFavoriteData();

        if ($userFavoriteData!=[]){
            $bookType=$userFavoriteData['bookType'];
            $userAgeRange=UserFavoriteData::findKeywordFromUserAgeRange($userFavoriteData['userAgeRange']);
            $favoriteCategory=$userFavoriteData['favoriteCategory'];
            $categoryId=Category::getCategoryId($favoriteCategory);

            //suggest three categories of books
            //best: One is that the community is the user's all three favorites
            //better: The second is a category that has two of those conditions
            //good: books that have one of the conditions

            $favoriteOffers=collect();

            $allBooks=StoreBook::getBooks();
            $bestOffers=Home::getBestOffers($allBooks,$bookType,$userAgeRange,$categoryId);
            if (sizeof($bestOffers)>=10){
                return $bestOffers;
            }else{
                $favoriteOffers=collect($bestOffers);
            }

            $betterOffers1=Home::getBetterOffers($allBooks,$userAgeRange,$categoryId,'ageCategory','categoryId');
            if (sizeof($betterOffers1)!=0){
                $favoriteOffers=collect(array_merge($favoriteOffers->toArray(),$betterOffers1))->unique();
                if (sizeof($favoriteOffers)>=10){
                    return $favoriteOffers;
                }
            }

            $betterOffers2=Home::getBetterOffers($allBooks,$bookType,$categoryId,'bookType','categoryId');
            if (sizeof($betterOffers2)!=0){
                $favoriteOffers=collect(array_merge($favoriteOffers->toArray(),$betterOffers2))->unique();
                if (sizeof($favoriteOffers)>=10){
                    return $favoriteOffers;
                }
            }

            $betterOffers3=Home::getBetterOffers($allBooks,$bookType,$userAgeRange,'bookType','ageCategory');
            if (sizeof($betterOffers3)!=0){
                $favoriteOffers=collect(array_merge($favoriteOffers->toArray(),$betterOffers3))->unique();
                if (sizeof($favoriteOffers)>=10){
                    return $favoriteOffers;
                }
            }

            $goodOffers1=Home::getGoodOffers($allBooks,$categoryId,'categoryId');
            if (sizeof($goodOffers1)!=0){
                $favoriteOffers=collect(array_merge($favoriteOffers->toArray(),$goodOffers1))->unique();
                if (sizeof($favoriteOffers)>=10){
                    return $favoriteOffers;
                }
            }

            $goodOffers2=Home::getGoodOffers($allBooks,$userAgeRange,'ageCategory');
            if (sizeof($goodOffers2)!=0){
                $favoriteOffers=collect(array_merge($favoriteOffers->toArray(),$goodOffers2))->unique();
                if (sizeof($favoriteOffers)>=10){
                    return $favoriteOffers;
                }
            }

            $goodOffers3=Home::getGoodOffers($allBooks,$bookType,'bookType');
            if (sizeof($goodOffers3)!=0){
                $favoriteOffers=collect(array_merge($favoriteOffers->toArray(),$goodOffers3))->unique();
                if (sizeof($favoriteOffers)>=10){
                    return $favoriteOffers;
                }
            }

            return $favoriteOffers;

        }else{
            return [];
        }

    }

    public static function userOffersWithImage($userId)
    {
        $storeBook=new StoreBook();
        $offers=Home::getUserExclusiveOffer($userId);

        $books=$offers->take(10)->toArray();

        $data=[];
        foreach ($books as $key=>$book){
            $storeBook->bookId=$book['id'];
            $data[$key]=$book;
            $data[$key]['image']=$storeBook->getBookImage();
        }
        return $data;
    }

    public static function getBestOffers($books,$bookType,$userAgeRange,$categoryId)
    {
        $bestOffers=[];
        foreach ($books as $book){
            if (($book['bookType']==$bookType) && ($book['ageCategory']==$userAgeRange) && ($book['categoryId']==$categoryId)){
                array_push($bestOffers,$book);
            }
        }
        return $bestOffers;
    }

    public static function getBetterOffers($books,$field1,$field2,$nameField1,$nameField2)
    {
        $betterOffers=[];
        foreach ($books as $book){
            if (($book[$nameField1]==$field1) && ($book[$nameField2]==$field2)){
                array_push($betterOffers,$book);
            }
        }
        return $betterOffers;
    }

    public static function getGoodOffers($books,$field,$nameField)
    {
        $goodOffers=[];
        foreach ($books as $book){
            if (($book[$nameField]==$field)){
                array_push($goodOffers,$book);
            }
        }
        return $goodOffers;
    }

    public static function getBooksWithDailyDiscounts($request)
    {
        $helper=new Libraries\Helper();
        $storeBook=new StoreBook();
        return $helper->paginate($request,$storeBook->getAllBooksWithIsDailyTrueAndNotExpired());
    }

    public static function getNewestBooks()
    {
        $books=Book::orderBy('created_at','DESC')
            ->take(10)->get();

        $storeBook=new StoreBook();
        $storeBook->addImageToBooks($books,'id');

        return $books;
    }

    public static function getBestSellingBooks()
    {
         $books=Book::orderBy('purchaseCount','DESC')
            ->take(10)->get();

        $storeBook=new StoreBook();
        $storeBook->addImageToBooks($books,'id');

        return $books;
    }

    public static function getGasedakOffers()
    {
        $storeBook=new StoreBook();
        $books=GasedakSuggestion::join('books','books.id','ghasedaksuggestions.bookId')
            ->orderBy('ghasedaksuggestions.created_at','DESC')
            ->take(10)
            ->get();

        $storeBook->addImageToBooks($books,'id');
        return $books;
    }

    public static function getTopStores()
    {
        return Store::orderBy('purchaseCount','DESC')
            ->take(10)->get();
    }

    public static function getMostDiscounts()
    {
        //they are sorted in descending order based on the discount.
        //if the discount is equal to several things,
        //they are sorted according to the latest sort.
        $storeBook=new StoreBook();
        $books=Book::all();
        $booksWithMinimumPriceInStores=$storeBook->addMinimumPriceAndDiscountToBooksFromStores($books);
        return $storeBook->orderByMostDiscount($booksWithMinimumPriceInStores);
    }

    public static function getLatestPublications()
    {
        $storeBook=new StoreBook();
        $publicationBooks=[];

        $publications=Store::where([['kind','انتشارات'],['isSuspended',0]])
            ->orderBy('created_at','DESC')
            ->take(2);

        //get newest publications id
        $publicationsIds=$publications->pluck('id');

        $index=0;
        foreach ($publications->get()->toArray() as $publication){
            $firstPublicationBooks=StoreBook::where('storebooks.storeId',$publicationsIds[$index])
                ->join('books','books.id','storebooks.bookId')
                ->orderBy('books.created_at','DESC')
                ->take(10)
                ->get();

            $publicationBooks[$index]=$publications->get()[$index];
            $storeBook->addImageToBooks($firstPublicationBooks);
            $publicationBooks[$index]['books']=$firstPublicationBooks;
            $index++;
        }
        return $publicationBooks;
    }

    public static function getSpecialPublicationBooks()
    {
        $storeBook=new StoreBook();

        //get special publication data
        $specialPublication=SpecialPublicationBook::join('stores','stores.id','specialpublicationbooks.specialPublicationId')
            ->orderBy('specialpublicationbooks.created_at','DESC')
            ->first();

        //get publication books data
        $specialPublication['publicationBooks']=SpecialPublicationBook::where('specialPublicationId',$specialPublication->id)
            ->join('books','books.id','specialpublicationbooks.bookId')
            ->orderBy('specialpublicationbooks.created_at','DESC')
            ->take(10)
            ->get();

        $storeBook->addImageToBooks($specialPublication['publicationBooks']);
        return $specialPublication;
    }
}
