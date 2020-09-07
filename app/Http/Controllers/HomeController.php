<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Offers;

use App\Message;
use App\Pages;

use DB;

use App\Blog;

use App\Http\Controllers\PagesController;

use App\Http\Middleware\LocaleMiddleware;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

     public function get_cities(Request $request)
     {

        $cities = DB::table('city')
            ->select('city.name')
            //->join('region', 'region.id_region', '=', 'city.id_region')
            ->where('city.id_country', '=', 82)
            ->where('city.name','LIKE',"%{$request->string}%")
            ->get();

            
            $out = '';
            
            foreach($cities as $city){
                
                $out .= '<p><a>'.$city->name.'</a></p>';
                
            }
            
            return response()->json(['data'=>$out]);
            
     }



    public function error_404()
    {
        return view('error_404');
    }


    public function index()
    {
        //$countries = DB::table('countries')->get();
        //$Pages = Pages::all();
        //return view('index',['countries' => $countries,'pages'=>$Pages]);
        
        //$main_page = DB::table('pages')->where('id', 27)->first();

        
        $films = DB::table('films')->get();
        
        return view('index',['films'=>$films]);
    }
    
    public function request_help($id='')
    {

        $lang = LocaleMiddleware::getLocale();
        
        //..echo $lang;

        switch ($lang) {
            case 'ru':
            default:
                $categories_lawyer = DB::table('categories_lawyer')
                ->where(['parent_id'=>1])
                ->select('name_ru as name')
                ->orderBy('name_ru')
                ->get();
                //echo "<div class='name_ul'><span>$category->name_ru</span></div>";
                break;
            case 'en':
                $categories_lawyer = DB::table('categories_lawyer')
                ->where(['parent_id'=>1])
                ->orderBy('name_en')
                ->select('name_en as name')
                ->get();
                //echo "<div class='name_ul'><span>$category->name_en</span></div>";
                break;
            case 'kz':
                $categories_lawyer = DB::table('categories_lawyer')
                ->where(['parent_id'=>1])
                ->orderBy('name_kz')
                ->select('name_kz as name')
                ->get();
                //echo "<div class='name_ul'><span>$category->name_kz</span></div>";
                break;
        }
        
        
        $lawyer_template='';
        $lawyer = DB::table('users')
                   ->where('id','=',$id)
                   ->where('role_id','=',5)
                   ->where('status','=',1)
                   ->first();
        
        if($lawyer) $lawyer_template = view('request_help.RequestLawyer',['lawyer'=>$lawyer])->render();
        
        return view('request_help',['categories_lawyer'=>$categories_lawyer,'lawyer'=>$lawyer,'lawyer_template'=>$lawyer_template]);
        
        //return view('request_help');
    }

    public function sudebnaya_zaschita()
    {
        
        $PagesController = new PagesController;
        
        $page = $PagesController->page(31);
        
        return $page;
        //return view('sudebnaya_zaschita');
    }

    public function companies()
    {
        return view('companies');
    }
    public function questions()
    {
        return view('questions');
    }
    public function business()
    {
        return view('business');
    }
    public function about()
    {
        //return view('about');

        $PagesController = new PagesController;
        
        $Page = $PagesController->page_clear(32);
        
        //print_r($page);
        //return $page;
        
        return view('about',['Page'=>$Page]);
        
    }
    
  
  
  
    
    
    
         public function messages(){
        
//$offers = App\Offers::all();
//return view('admin.offers', ['offers' => $offers]); 
            $user = Auth::user();

            $offers = DB::table('messages')
            
            //->where('offer_id', $user->id)
            
            ->where('from_user_id','!=', $user->id)
            ->where('to_user_id', $user->id)
            
            //->where('from_user_id', $password)
            //->join('users', 'users.id', '=', 'messages.user_id')
            //->select('offers.*','users.name')
            ->groupBy('from_user_id')
            ->get();
            

           foreach($offers as $key=>$offer){


                $offer_images = DB::table('offers_images')
                ->where('offers_images.offer_id', $offer->offer_id)
                ->join('images', 'images.id', '=', 'offers_images.image_id')
                ->select('images.filename')
                ->get();

                $offers[$key]->images = $offer_images;

           }
           
            //$messages = '';
            //$id = '';
            
            //dd($offers);
            
            return view('messages', ['offers' => $offers]);        

            // return view('admin.offers');
         }    
    


         public function messages_offer($id,$id_m){

            $user = Auth::user();

            $offers = DB::table('messages')
            ->where('from_user_id','!=', $user->id)
            ->where('to_user_id', $user->id)
            ->groupBy('from_user_id')
            ->get();
            

           foreach($offers as $key=>$offer){

                $offer_images = DB::table('offers_images')
                ->where('offers_images.offer_id', $offer->offer_id)
                ->join('images', 'images.id', '=', 'offers_images.image_id')
                ->select('images.filename')
                ->get();

                $offers[$key]->images = $offer_images;

           }
           
            $messages = DB::table('messages')
            ->where('offer_id', $id)
            ->whereIn('from_user_id', [$id_m,$user->id])
            ->whereIn('to_user_id', [$id_m,$user->id])
            ->get();
            
                
            if($messages->count()){
            
                return view('messages', ['offers' => $offers,'messages' => $messages,'id'=>$id,'id_m'=>$id_m,'user_id'=>$user->id]);        
    
            }else{
                
                return redirect()->route('messages');
                
            }

            // return view('admin.offers');
         } 


 
    public function add_message(Request $request,$id,$id_m){ 
 
            
            
            $user = Auth::user();
            
            
            $Offer = Offers::find($id);

/*            $offer_images = DB::table('offers_images')
            ->where('offers_images.offer_id', $Offer->id)
            ->join('images', 'images.id', '=', 'offers_images.image_id')
            ->select('images.filename')
            ->get();
*/            



            
            
            //DB::table('files')->insert($sql);
            
            $Message = new Message();
            
            $Message->offer_id = $Offer->id;
            
            $Message->message = $request->areas;
            
            //if($file){
            //$Message->file_id = $File->id;
            //}
            
            $Message->from_user_id = $user->id;

            $Message->to_user_id = $id_m;
            
            
            
            $Message->save();

            
            return redirect()->route('messages_offer',[$id,$id_m]);
 
 
    }
 
 
 
 
 

     
    
    
    
}
