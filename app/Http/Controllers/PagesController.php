<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pages;

use DB;

class PagesController extends Controller
{


    
    
         static function pages($ids=array()){


            $Pages = DB::table('pages')->whereIn('id',$ids)->get();

/*            $Page_images = DB::table('pages_images')
            ->where('pages_images.page_id', $Page->id)
            ->join('images', 'images.id', '=', 'pages_images.image_id')
            ->select('images.filename')
            ->get();

            //dd($Offer);
            //dd($offers_images);
            
            $Pages = Pages::all();
            
            return view('page', ['Page' => $Page,'page_images'=>$Page_images,'pages'=>$Pages,'active_page'=>$id]);

*/     

        return $Pages;

   }



         public function page($id){


            $Page = DB::table('pages')->where('id', $id)->first();

            $Page_images = DB::table('pages_images')
            ->where('pages_images.page_id', $Page->id)
            ->join('images', 'images.id', '=', 'pages_images.image_id')
            ->select('images.filename')
            ->get();

            //dd($Offer);
            //dd($offers_images);
            
            $Pages = Pages::all();
            
            return view('page', ['Page' => $Page,'page_images'=>$Page_images,'pages'=>$Pages,'active_page'=>$id]);

        }


         public function page_clear($id){


            $Page = DB::table('pages')->where('id', $id)->first();

/*            
            $Page_images = DB::table('pages_images')
            ->where('pages_images.page_id', $Page->id)
            ->join('images', 'images.id', '=', 'pages_images.image_id')
            ->select('images.filename')
            ->get();

*/            //dd($Offer);
            //dd($offers_images);
            
            //$Pages = Pages::all();
            
            return $Page;

        }


         public function page_alias($page_alias){
            
            //print_r($req->all());
            echo $page_alias;
            
/*            $Page = DB::table('pages')->where('id', $id)->first();

            $Page_images = DB::table('pages_images')
            ->where('pages_images.page_id', $Page->id)
            ->join('images', 'images.id', '=', 'pages_images.image_id')
            ->select('images.filename')
            ->get();
            
            return view('page', ['Page' => $Page,'page_images'=>$Page_images,'pages'=>$Pages,'active_page'=>$id]);
*/            
            
         }


}
