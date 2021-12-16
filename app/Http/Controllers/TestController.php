<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Libraries;


class TestController extends Controller
{
    //
    public function test(Request $request)
    {
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

    }
}
