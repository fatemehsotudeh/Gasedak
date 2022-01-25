<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Book;
use App\Models\Category;
use App\Models\GasedakSuggestion;
use App\Models\SpecialPublicationBook;
use App\Models\Store;
use App\Models\UserFavoriteData;
use Illuminate\Http\Request;

use App\Libraries;

class HomeController extends Controller
{
    //
    public function homeAll(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $data=[];

        try {
            $data['banners']=$this->getBanners();
            $data['userExclusiveOffers']=$this->getUserExclusiveOffer($identifiedUser->id)->take(10)->values();
            $data['newestBooks']=$this->getNewestBooks();
            $data['bestSellingBooks']=$this->getBestSellingBooks();
            $data['gasedakOffers']=$this->getGasedakOffers();
            $data['topStores']=$this->getTopStores();
            $data['mostDiscounts']=$this->getMostDiscounts();
            $data['latestPublications']=$this->latestPublications();

            return response()->json(['data' =>$data,'message'=>'return categories and banners successfully'],200);

        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }

    }

    public function homeBook(Request $request)
    {
        //decode bearer token
        $helper=new Libraries\Helper();
        $identifiedUser=$helper->decodeBearerToken($request->bearerToken());

        $data=[];

        try {
            $data['banners']=$this->getBanners();
            $data['userExclusiveOffers']=$this->getUserExclusiveOffer($identifiedUser->id)->take(10)->values();
            $data['newestBooks']=$this->getNewestBooks();
            $data['bestSellingBooks']=$this->getBestSellingBooks();
            $data['mostDiscounts']=$this->getMostDiscounts();
            $data['gasedakOffers']=$this->getGasedakOffers();
            $data['specialPublicationBooks']=$this->getSpecialPublicationBooks();

            return response()->json(['data' =>$data,'message'=>'return categories and banners successfully'],200);
        }catch (\Exception $e){
            return response()->json(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    public function getSpecialPublicationBooks()
    {
        //get special publication data
        $specialPublication=SpecialPublicationBook::join('stores','stores.id','specialpublicationbooks.specialPublicationId')
            ->orderBy('specialpublicationbooks.created_at','DESC')
            ->first()
            ->makeHidden([
                'email',
                'password',
                'IBAN',
                'username'
            ]);

        //get publication books data
        $specialPublication['publicationBooks']=SpecialPublicationBook::where('specialPublicationId',$specialPublication->id)
            ->join('books','books.id','specialpublicationbooks.bookId')
            ->orderBy('specialpublicationbooks.created_at','DESC')
            ->take(10)
            ->get();

        return $specialPublication;
    }

    public function getUserExclusiveOffer($userId)
    {
        $helper=new Libraries\Helper();

        $userFavoriteData=UserFavoriteData::where('userId',$userId);

        if ($userFavoriteData->exists()){
            //get the user favorite information needs to know
            $bookType=$userFavoriteData->pluck('bookType')->first();
            $userAgeRange=$userFavoriteData->pluck('userAgeRange')->first();
            $favoriteCategory=$userFavoriteData->pluck('favoriteCategory')->first();
            $categoryId=Category::where('title',$favoriteCategory)->pluck('id')->first();

            //separate keyword from user favorites data
            $bookType=$helper->splitSentence($bookType,0);
            $userAgeRange=$helper->splitSentenceAgeRange($userAgeRange,0);

            //save the query condition to prevent duplication
            $typeCondition=['bookType',$bookType];
            $ageCategoryCondition=['ageCategory',$userAgeRange];
            $favoriteCategoryCondition=['categoryId',$categoryId];

            //suggest three categories of books
            //best: One is that the community is the user's all three favorites
            //better: The second is a category that has two of those conditions
            //good: books that have one of the conditions
            $bestFavoriteOffers=Book::where([$typeCondition,$ageCategoryCondition,$favoriteCategoryCondition]);

            $betterFavoriteOffers=Book::where([$typeCondition,$ageCategoryCondition])
                ->orWhere([$typeCondition,$favoriteCategoryCondition])
                ->orWhere([$ageCategoryCondition,$favoriteCategoryCondition]);

            $goodFavoriteOffers=Book::where('bookType',$bookType)
                ->orWhere('ageCategory',$userAgeRange)
                ->orWhere('categoryId',$categoryId);

            //if the number of the best offers is ten, we will show the same ones as the user assignment offer
            //If not, we will go to other cases so that we can finally show 10 books to the user.
            $favoriteOffers=collect();
            if ($bestFavoriteOffers->exists()){
                if(sizeof($bestFavoriteOffers->get())>=10){
                    return $bestFavoriteOffers->get();
                }else{
                    $favoriteOffers=$bestFavoriteOffers->get();
                }
            }

            if ($betterFavoriteOffers->exists()){
                $favoriteOffers=collect(array_merge($favoriteOffers->toArray(),$betterFavoriteOffers->get()->toArray()))->unique();
                if (sizeof($favoriteOffers)>=10){
                    return $favoriteOffers;
                }
            }

            if ($goodFavoriteOffers->exists()){
                $favoriteOffers=collect(array_merge($favoriteOffers->toArray(),$goodFavoriteOffers->get()->toArray()))->unique();
                if (sizeof($favoriteOffers)>=10){
                    return $favoriteOffers;
                }
            }
            return $favoriteOffers;
        }
    }

    public function getNewestBooks()
    {
        return Book::orderBy('created_at','DESC')
            ->take(10)->get();
    }

    public function getBestSellingBooks()
    {
        return Book::orderBy('purchaseCount','DESC')
            ->take(10)->get();
    }

    public function getTopStores()
    {
        return Store::orderBy('purchaseCount','DESC')
            ->take(10)->get();
    }

    public function getMostDiscounts()
    {
        //they are sorted in descending order based on the discount.
        //if the discount is equal to several things,
        //they are sorted according to the latest sort.
        return Book::orderBy('percentDiscountAmount','DESC')
            ->orderBy('created_at','DESC')
            ->take(10)
            ->get();
    }

    public function getGasedakOffers()
    {
        return GasedakSuggestion::join('books','books.id','ghasedaksuggestions.bookId')
            ->orderBy('ghasedaksuggestions.created_at','DESC')
            ->take(10)
            ->get();
    }

    public function latestPublications()
    {
        $publicationBooks=[];

        $publications=Store::where('kind','انتشارات')
            ->orderBy('created_at','DESC')
            ->take(2);

        //get newest publications id
        $publicationsIds=$publications->pluck('id');

        $index=0;
        foreach ($publications->get()->toArray() as $publication){
            $firstPublicationBooks=Book::where('books.storeId',$publicationsIds[$index])
                ->orderBy('books.created_at','DESC')
                ->take(10)
                ->get();

            $publicationBooks[$index]=$publications->get()[$index];
            $publicationBooks[$index]['books']=$firstPublicationBooks;
            $index++;
        }
        return $publicationBooks;
    }

    public function getBanners()
    {
        return Banner::orderBy('created_at','DESC')
            ->take(10)
            ->get();
    }
}
