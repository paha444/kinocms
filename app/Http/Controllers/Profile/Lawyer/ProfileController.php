<?php

namespace App\Http\Controllers\Profile\Lawyer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Image;

use Illuminate\Database\Eloquent\Model;

use App\Offers;
use App\Blog;

//use App\Role;

use App\User;

use Illuminate\Support\Facades\Hash;


use App\Models\Country;
use App\Models\Region;

use Illuminate\Support\Facades\Auth;

use DB;

use File;

use Carbon\Carbon;

use App\Http\Middleware\LocaleMiddleware;

use App\Http\Controllers\Profile\WooppayController;

class ProfileController extends Controller
{
 
 
        public function tag(Request $req){
            
            $user = Auth::user();
            
            $post = $req->all();
            
            $lang = LocaleMiddleware::getLocale();
            
            

                
            switch ($post['oper']) {
                case 'add':
                        DB::table('users_lawyer_categories')->insert([
                        'user_id'=>$user->id,
                        'categories_lawyer_id'=>$post['id'],
                        ]);
                    break;
                case 'del':
                        DB::table('users_lawyer_categories')->where([
                        'user_id'=>$user->id,
                        'categories_lawyer_id'=>$post['id'],
                        ])->delete();
                    break;

            }
            
            

            
            $lawyer_categories = DB::table('users_lawyer_categories')
            ->Join('categories_lawyer', 'categories_lawyer.id', '=', 'users_lawyer_categories.categories_lawyer_id')
            ->select('categories_lawyer.*')
            ->where(['user_id'=>$user->id])
            ->groupBy('users_lawyer_categories.categories_lawyer_id')
            ->get();            
            
            
            $result = view('profile.lawyer.lawyer_categories',['lawyer_categories'=>$lawyer_categories,'lang'=>$lang])->render();
            
            return response()->json(['result'=>$result]);
            
        
        }   

 

    
/*    
         public function callback(Request $req){
            
                $user = Auth::user();
                $payment_id = $req->input('payment_id');
                
                $payment = DB::table('users_replenishment')
                            ->where('users_replenishment.id', $payment_id)
                            ->where('users_replenishment.to_user_id', $user->id)
                           
                            ->select('users_replenishment.*')
                            ->first();   

                DB::table('users_replenishment')->update(['status' => 1]);
                DB::table('users')
                ->where('id', $user->id)
                ->update(['amount' => ($user->amount+$payment->summ)]);
                
                //print_r($payment); die;
                     
                return redirect()->route('profile_payment_lawyer',$payment_id);
         }
         
      */   
             
         public function profile(Request $req, $affairs=''){
            
            
            $data = $req->all();
            
            if(isset($data['tab'])){
                $tab = $data['tab'];
            }else{
                $tab = '';
            }

            //print_r($data); die;
            
            $user = Auth::user();
            
            $offers = array();

            $where = [];
            $where[] = ['offers_lawyers.to_user_id','=', $user->id];


            switch ($affairs) {
            
                case 'in_work':
                    //$status = 'processing';
                    $where[] = ['offers_lawyers.status','=', 'processing'];

                    
                break;
                case 'completed':
                    //$status = 'completed';
                    $where[] = ['offers_lawyers.status','=', 'completed'];

                break;
                case 'everything':
                default:
                    //$status = null;
                break;
                
            }
            
//////////////////////////////
            
            $offers_selected = DB::table('offers_lawyers')
            ->where($where)
            ->leftJoin('offers', 'offers.id', '=', 'offers_lawyers.offer_id')
            ->select('offers.id','offers.user_id','offers.RequestCaption','offers.price','offers.status as offer_status','offers_lawyers.status','offers_lawyers.cost','offers_lawyers.time')
            ->groupBy('offers_lawyers.offer_id')
            ->orderBy('offers.id','DESC')
            ->get();

            foreach($offers_selected as $key=>$offer){

                $messages = DB::table('messages')
                ->where('offer_id', $offer->id)
                ->whereIn('from_user_id', [$offer->user_id,$user->id])
                ->whereIn('to_user_id', [$offer->user_id,$user->id])
                ->get();
 
                $offers_selected[$key]->messages_count=$messages->count();
            }
            
/////////////////////////

            $offers_all = DB::table('offers')
            ->leftJoin('offers_lawyers', function ($join) use($user){
                $join->on('offers_lawyers.offer_id', '=', 'offers.id')
                     ->where('offers_lawyers.to_user_id', $user->id);
            })
            ->where('offers.RequestRecipients', 'all')
            ->where('offers.status', 'new')
            ->whereNull('offers_lawyers.id')
            ->select('offers.*')
            ->groupBy('offers.id')
            ->orderBy('offers.id','DESC')
            ->get();

            foreach($offers_all as $key=>$offer){

                $messages = DB::table('messages')
                ->where('offer_id', $offer->id)
                ->whereIn('from_user_id', [$offer->user_id,$user->id])
                ->whereIn('to_user_id', [$offer->user_id,$user->id])
                ->get();
 
                $offers_all[$key]->messages_count=$messages->count();
            }
///////////////////////   


            $offers_my = DB::table('offers')
            ->leftJoin('offers_lawyers', function ($join) use($user){
                $join->on('offers_lawyers.offer_id', '=', 'offers.id')
                     ->where('offers_lawyers.to_user_id', $user->id);
            })
            //->where('offers.RequestRecipients', 'all')
            //->where('offers.status', 'new')
            ->where('offers.user_id', $user->id)
            
            //->whereNull('offers_lawyers.id')
            ->select('offers.*')
            ->groupBy('offers.id')
            ->orderBy('offers.id','DESC')
            ->get();

            foreach($offers_my as $key=>$offer){

                $messages = DB::table('messages')
                ->where('offer_id', $offer->id)
                ->whereIn('from_user_id', [$offer->user_id,$user->id])
                ->whereIn('to_user_id', [$offer->user_id,$user->id])
                ->get();
 
                $offers_my[$key]->messages_count=$messages->count();
            }


///////////////////////         

            $reviews = DB::table('reviews')
            
            ->leftJoin('users', 'users.id', '=', 'reviews.from_user_id')
            
            ->where('to_user_id', $user->id)
            
            ->select('users.*',
            'reviews.offer_id as rev_offer_id',
            'reviews.message as rev_message',
            'reviews.created_at as rev_created_at')
            ->get();

            
             $blogs = DB::table('blogs')->where('user_id', $user->id)->get();

               foreach($blogs as $key=>$post){
    
    
                    $blog_images = DB::table('blogs_images')
                    ->where('blogs_images.blog_id', $post->id)
                    ->join('images', 'images.id', '=', 'blogs_images.image_id')
                    ->select('images.filename')
                    ->get()->toArray();
    
                    $blogs[$key]->images = $blog_images;
    
               }
            

             return view('profile.lawyer.profile',['user'=>$user,'offers_selected' => $offers_selected,'offers_all' => $offers_all,'offers_my'=>$offers_my,'reviews'=>$reviews,'blogs'=>$blogs,'tab'=>$tab]);


         }




         public function get_money_submit(Request $req){

            $user = Auth::user();

            $summ = $req->input('summ');
            $paymentmethod = $req->input('paymentmethod');
            
            //echo $paymentmethod; die;
            
            if($user->amount>=$summ){



                DB::table('users')
                ->where('id', $user->id)
                ->update(['amount'=>($user->amount - $summ)]);                 

                DB::table('users_payments')->insert(
                ['from_user_id' => $user->id, 
                 'summ' => $summ,
                 'payment_system'=>$paymentmethod,
                 'created_at'=>Carbon::now(),
                 
                ]);

                DB::table('users_transactions_report')->insert(
                ['user_id'=>$user->id,
                 'summ'=>$summ,
                 'type_transaction'=>'-',
                 'message'=>'Вывод средств',
                 'created_at'=>Carbon::now(),
                 'updated_at'=>Carbon::now(),
                 ]); 
                
                
                return redirect()->route('profile_add_money_lawyer',['tab'=>'withdraw'])->with('message', 'Средства на вывод добавлены');
                
            }else{
                
                return redirect()->route('profile_add_money_lawyer',['tab'=>'withdraw'])->with('message', 'Недостаточно средств');
                
            }
            
            //return redirect()->route('profile_add_money_lawyer')->with('message', 'Profile updated!');

         }



         public function add_money(Request $req){
            
            //print_r($req);
            //echo $req->tab;
            
            $user = Auth::user();
           
            $user_payments = DB::table('users_payments')
            ->where('from_user_id', $user->id)
            ->orderBy('id','DESC')
            ->get();

          
          
            return view('profile.lawyer.add_money',['user_payments'=>$user_payments,'tab'=>$req->tab]);
        
        
         }



         public function add_money_submit(Request $req){

            $user = Auth::user();

            $summ = $req->input('summ');
            $paymentmethod = $req->input('paymentmethod');
            
            //echo $paymentmethod; die;
            
            if($summ>0){
                
                $id = DB::table('users_replenishment')->insertGetId(
                    ['to_user_id' => $user->id, 'summ' => $summ, 'payment_system' => $paymentmethod]
                );
                
                return redirect()->route('profile_payment_lawyer',$id);
            
            }else{
                
                return redirect()->route('profile_add_money_lawyer');
                
            }

         }

         public function payment_return($id,$key){         

            if ($key == md5($id.'jLbjQ1E0P4')) {
            
/*              DB::table('users_replenishment')
                ->where('id', $id)
                ->update(['status' => 1]);  
*/            
                //$this->payment_check($id);

            $Wooppay = new WooppayController();
            $operation_data = $Wooppay->check($id);
 

            }
            

            
/*            DB::table('users_replenishment')
            ->where('id', $payment->id)
            ->update(['status' => 1]);  

            
            //$Wooppay = new WooppayController();
            //$operation_data = $Wooppay->check($id);
*/            
            return redirect()->route('profile_payment_lawyer',$id);
         
         }



         public function payment_check($id){         
            
            $Wooppay = new WooppayController();
            $operation_data = $Wooppay->check($id);
            
            return redirect()->route('profile_payment_lawyer',$id);
         
         }

         public function payment($id){
            //return view('profile.dashboard');

             //$user = Auth::user();
 
            $user = Auth::user();
            
            //$Offer->type_offer = $req->input('type_offer');
            
            if(isset($id)){
            
            $payment = DB::table('users_replenishment')
            ->where('users_replenishment.id', $id)
            ->where('users_replenishment.to_user_id', $user->id)
            ->join('users', 'users.id', '=', 'users_replenishment.to_user_id')
            
            ->select('users_replenishment.*',
                     'users.name',
                     'users.email')
            ->first();
            
                if(isset($payment)){

                    if($payment->operationId){
                        
                        $operationUrl = $payment->result;
                        
                        return view('profile.lawyer.payment',['payment'=>$payment,'operationUrl'=>$operationUrl]);
                            
                    }else{

                        $Wooppay = new WooppayController();
                        $operationUrl = $Wooppay->invoice($payment);
                        
                        return redirect()->route('profile_payment_lawyer',$id);
                        
                    }

                    
                    //return view('profile.lawyer.payment',['payment'=>$payment]);
    
                }else{
                    
                    return redirect()->route('profile_add_money_lawyer');
                    
                }

            
            }else{
                
            return redirect()->route('profile_add_money_lawyer');
                
            }

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
            
            return view('profile.messages', ['offers' => $offers]);        

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
            
                return view('profile.messages', ['offers' => $offers,'messages' => $messages,'id'=>$id,'id_m'=>$id_m,'user_id'=>$user->id]);        
    
            }else{
                
                return redirect()->route('profile_messages');
                
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

            
            return redirect()->route('profile_messages_offer',[$id,$id_m]);
 
 
    }
 
 




         public function get_cities(Request $request)
         {

            $cities = DB::table('city')
                ->select('city.name')
                ->join('region', 'region.id_region', '=', 'city.id_region')
                ->where('region.name', '=', $request->region)
                ->where('city.name','LIKE',"%{$request->string}%")
                ->get();

                
                $out = '';
                
                foreach($cities as $city){
                    
                    $out .= '<p><a>'.$city->name.'</a></p>';
                    
                }
                
                return response()->json(['data'=>$out]);
                
         }




         public function get_regions(Request $request)
         {

            $regions = DB::table('region')
                ->select('region.name')
                ->join('countries', 'countries.id_country', '=', 'region.id_country')
                ->where('countries.name', '=', $request->country)
                ->where('region.name','LIKE',"%{$request->string}%")
                ->get();

                
                $out = '';
                
                foreach($regions as $region){
                    
                    $out .= '<p><a>'.$region->name.'</a></p>';
                    
                }
                
                return response()->json(['data'=>$out]);
                
         }




         public function get_countries(Request $request)
         {
                //public $primaryKey = 'name';
                
                //echo $request->string;
                
                $countries = Country::where('name', 'LIKE', "%{$request->string}%")->get(); //where('name', $request->string)->get();
                
                //dd($countries);
                
                $out = '';
                
                foreach($countries as $country){
                    
                    $out .= '<p><a>'.$country->name.'</a></p>';
                    
                }
                
                return response()->json(['data'=>$out]);
                
         }





         public function rental_request(){
            return view('profile.rental_request');
         }


         public function dashboard(){
            return view('profile.dashboard');
         }

         public function offers(){
             return view('profile.offers');
         }

      //   public function rent(){
      //       return view('profile.rent');
      //   }

         public function employers(){
            
             $employers = DB::table('users')->where('role_id', 3)->get();   
            
             return view('profile.employers',['employers' => $employers]);
         }

         public function values(){
             return view('profile.values');
         }

         public function users(){

             $users = DB::table('users')
             ->select('users.*','roles.name_ru')
             ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
             ->where('users.id','!=', 1)
             ->get();   
            
           
             $roles = DB::table('roles')->where('id','!=', 10)->get();   
            

             return view('profile.users',['users' => $users,'roles' => $roles]);
         }

         public function blog(){
            
            
             $blog = Blog::all();
            
            
             return view('profile.blog', ['blogs' => $blog]);
            
             //return view('profile.blog');
         }
         
         public function blog_edit($id){
            
            
             //$blog = Blog::all();


            //$user = Auth::user();
            
            $post = DB::table('blogs')->where('id', $id)->first();
            
/*            $offer_images = DB::table('offers_images')
            ->where('offers_images.offer_id', $Offer->id)
            ->join('images', 'images.id', '=', 'offers_images.image_id')
            ->select('images.filename')
            ->get();
*/                        
            //dd($Offer);
            //dd($offers_images);
            
            //return view('profile.offers_edit', ['offer' => $Offer,'offer_images'=>$offer_images]);             
            
             return view('profile.blog_edit', ['post' => $post]);
            
             //return view('profile.blog');
         }
         
         
         
         
         
         
         
         
         
         
         
         
         
         

         public function settings(){
            
 //           $roles = DB::table('roles')->where('id', $id)->first();    
            
            
  //           return view('profile.settings');



            $roles = DB::table('roles')
            
            ->where('name', '!=', 'admin')

            //->join('users', 'users.id', '=', 'offers.user_id')
            //->select('offers.*','users.name')
            ->get();
            
            
        
            return view('profile.settings', ['roles' => $roles]); 


         }



         public function settings_roles_submit(Request $request){
            

            DB::table('roles')
            ->where('name', '!=', 'admin')
            ->update(['suggestions' => 0,'rental_requests' => 0,'employees' => 0,'values' => 0,
                      'users' => 0,'articles' => 0,'settings' => 0]);
            
            if($request->suggestions):
                foreach($request->suggestions as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['suggestions' => 1]);
                }
            endif;

            if($request->rental_requests):
                foreach($request->rental_requests as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['rental_requests' => 1]);
                }
            endif;
            
            if($request->employees):    
                foreach($request->employees as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['employees' => 1]);
                }
            endif;
            
            if($request->values):
                foreach($request->values as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['values' => 1]);
                }
            endif;
            
            if($request->users):
                foreach($request->users as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['users' => 1]);
                }
            endif;
            
            if($request->articles):
                foreach($request->articles as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['articles' => 1]);
                }
            endif;
            
            if($request->settings):
                foreach($request->settings as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['settings' => 1]);
                }
            endif;


  
            return redirect()->route('settings');
  
         }





         public function offers_add(Request $request){

            $result = $request->session()->all();//получаем данные из сессии
    	    $token = $result['_token'];


             return view('profile.offers_add',['token'=>$token]);
         }


         public function offers_add_submit(Request $req)
         {

            $Offer = new Offers();
            
            $Offer->message = $req->input('message');
            
            $Offer->save();
            
            
            return redirect()->route('offers');
             
         }

  
        public function add_image(Request $request)
        {
            //
    
            $image = $request->file('file');
            $avatarName = $image->getClientOriginalName();
            $image->move(public_path('images/'),$avatarName);
             
            $imageUpload = new Image();
            $imageUpload->filename = $avatarName;
            $imageUpload->save();
            return response()->json(['success'=>$avatarName,'id'=>$imageUpload->id]);
            
        }

        public function delete_image(Request $request)
        {
            
            
            $Image = DB::table('images')->where('filename', $request->filetodelete)->first();
            
            DB::delete('delete from images where filename = ?',[$request->filetodelete]);
            
            $image_path = public_path('images').'/'.$request->filetodelete;
            
            //echo $image_path;
  
              if(File::exists($image_path)) {
                    File::delete($image_path);
              }
              
              return response()->json(['id'=>$Image->id]);
            
            //$filetodelete = $request->file('filetodelete');
            
            //echo $filetodelete;
            
            //echo $request->filetodelete;
            
          //  dd($request->all());
            
            //print_r($request);
            
        
        }


        public function offers_delete_file(Request $request)
        {
            
            
            $Offer = Offers::where('filename', $request->filetodelete)->first();
            //$Offer = $Offer->where('image', $path);
            $Offer->filename = '';
            $Offer->save();
                        
            $image_path = public_path('files').'/'.$request->filetodelete;
            
            //echo $image_path;
  
              if(File::exists($image_path)) {
                    File::delete($image_path);
              }
            
        
        }




        public function edit_profile(){
            
            
            $user = Auth::user();
            
                

            $lang = LocaleMiddleware::getLocale();
            
            //..echo $lang;
    
            switch ($lang) {
                case 'ru':
                default:
                    $categories_lawyer = DB::table('categories_lawyer')
                    ->where(['parent_id'=>1])
                    ->select('id','name_ru as name')
                    ->orderBy('name_ru')
                    ->get();
                    //echo "<div class='name_ul'><span>$category->name_ru</span></div>";
                    break;
                case 'en':
                    $categories_lawyer = DB::table('categories_lawyer')
                    ->where(['parent_id'=>1])
                    ->orderBy('name_en')
                    ->select('id','name_en as name')
                    ->get();
                    //echo "<div class='name_ul'><span>$category->name_en</span></div>";
                    break;
                case 'kz':
                    $categories_lawyer = DB::table('categories_lawyer')
                    ->where(['parent_id'=>1])
                    ->orderBy('name_kz')
                    ->select('id','name_kz as name')
                    ->get();
                    //echo "<div class='name_ul'><span>$category->name_kz</span></div>";
                    break;
            }


            $lawyer_categories = DB::table('users_lawyer_categories')
            ->Join('categories_lawyer', 'categories_lawyer.id', '=', 'users_lawyer_categories.categories_lawyer_id')
            ->select('categories_lawyer.*')
            ->where(['user_id'=>$user->id])
            ->groupBy('users_lawyer_categories.categories_lawyer_id')
            ->get();            
            
            
             return view('profile.lawyer.edit_profile',['user'=>$user,'categories_lawyer'=>$categories_lawyer,'lawyer_categories'=>$lawyer_categories,'lang'=>$lang]);
        }
        
         
         
         
          public function edit_profile_submit(Request $req){
            
            
            $u = Auth::user();
            
            $file = $req->file('avatar');
            
            if($file){
                if($u->avatar){
                    $image_path = public_path('images/avatars').'/'.$u->avatar;
                      if(File::exists($image_path)) {
                            File::delete($image_path);
                      }
                }

                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                $file->move(public_path('images/avatars'),$fileName); 
            }
            
            $User = User::find($u->id);

            if(isset($fileName))
                $User->avatar = $fileName;

            if($req->input('password')){
                $password = Hash::make($req->input('password'));
                $User->password = $password;
            }
            $User->email = $req->input('email');


            $User->fullname = $req->input('fullname');
            $User->family = $req->input('family');
            $User->surname = $req->input('surname');
            $User->date = $req->input('date');
            $User->work_experience = $req->input('work_experience');
            //$User->month = $req->input('month');
            $User->year = $req->input('year');
            
            if($req->input('sex_m')=='on'){ 
                $User->sex='m';
            }
            if($req->input('sex_w')=='on'){ 
                $User->sex='w';
            }
            //$Offer->lift = $req->input('lift') ? 1 : 0;
            //$Offer->parking = $req->input('parking') ? 1: 0;
            //$Offer->area = $req->input('area');
            //$Offer->rooms = $req->input('rooms');
            //$User->country = $req->input('country');
            //$User->region = $req->input('region');
            $User->city = $req->input('city');
            //$User->district = $req->input('district');
            //$Offer->street = $req->input('street');
            //$Offer->house = $req->input('house');
            
            $User->facebook = $req->input('facebook');
            $User->vk = $req->input('vk');
            $User->instagram = $req->input('instagram');

            $User->phone = $req->input('phone');
            $User->telegram = $req->input('telegram');
            $User->card = $req->input('card');
            $User->wooppay = $req->input('wooppay');
            $User->about = $req->input('about');

            $resume = $req->file('resume');
            
                if(isset($resume)){
    
                $extentions = ['doc','docx'];
                $ext = $resume->getClientOriginalExtension();
                
                    if(in_array($ext,$extentions)){
        
                        $file_path = public_path('files').'/'.$u->resume;
                            if(File::exists($file_path)) {
                                File::delete($file_path);
                            }
        
                        $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$resume->getClientOriginalExtension();
                        $resume->move(public_path('files'),$fileName); 
                        $User->resume = $fileName;
                    }
                }
                
            $User->save();
            
                        
           return redirect()->route('profile_edit_profile_lawyer')->with('message', 'Ваши данные обновлены');

         }


        public function delete_resume()
        {
            $u = Auth::user();
        
            $file_path = public_path('files').'/'.$u->resume;
            
            if(File::exists($file_path)) {
                File::delete($file_path);
            }
            
            $User = User::find($u->id);
            
            $User->resume = '';
            
            $User->save();
            
            return redirect()->route('profile_edit_profile_lawyer');
        
} 



          public function user_add(){
            
            // $user = User::find($id);
             
             //$user = Auth::user();
            
                
            
            
            
             return view('profile.user_add');
         }



          public function user_add_submit(Request $req){
            
            
             //$user = Auth::user();
            
               //dd($request);

            //$u = Auth::user();
            //$Offer = new Offers();
            
            $User = new User();
  
  //dd($id);

            $file = $req->file('avatar');
            
            if($file){

            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('images/avatars'),$fileName); 

            
            }
            
            //dd($file);
            
    
            

            if(isset($fileName))
                $User->avatar = $fileName;



            $User->name = $req->input('login');
            
            if($req->input('password')){
                $password = Hash::make($req->input('password'));
                $User->password = $password;
            }
            $User->email = $req->input('email');
            
            $User->role_id = $req->input('role_id');
            


            $User->fullname = $req->input('fullname');
            $User->family = $req->input('family');
            $User->surname = $req->input('surname');
            $User->date = $req->input('date');
            $User->month = $req->input('month');
            $User->year = $req->input('year');
            
            if($req->input('sex_m')=='on'){ 
                $User->sex='m';
            }
            if($req->input('sex_w')=='on'){ 
                $User->sex='w';
            }
            //$Offer->lift = $req->input('lift') ? 1 : 0;
            //$Offer->parking = $req->input('parking') ? 1: 0;
            
            
            //$Offer->area = $req->input('area');
            //$Offer->rooms = $req->input('rooms');
            $User->country = $req->input('country');
            $User->region = $req->input('region');
            //$Offer->city = $req->input('city');
            $User->district = $req->input('district');
            //$Offer->street = $req->input('street');
            //$Offer->house = $req->input('house');
            
            $User->facebook = $req->input('facebook');
            $User->vk = $req->input('vk');
            $User->instagram = $req->input('instagram');

            $User->phone = $req->input('phone');
            $User->map_coordinates = $req->input('map_coordinates');

            //$Offer->furniture = $req->input('furniture') ? 1: 0;
            $User->status = $req->input('status');
            //$Offer->comment = $req->input('comment');
            
            
            
            //$Offer->user_id = $user->id;
            
            //$Offer->views = 0;
            
            $User->save();
            
/*            
            $images = $req->images;
            
            if($images){
                //while($i < count($req->input('images'))){
                foreach($images as $image){
                
                    $sql[] = array(
                      'offer_id' => $Offer->id,
                      'image_id' => $image,
                    );
                  //  $i++;
                }
                
                
                DB::table('offers_images')->insert($sql);
            }
 
 */
            
            
                        
           //return redirect()->route('admin.edit_profile');



           return view('profile.user_edit',['user'=>$User]); 
            
            
             //return view('admin.edit_profile',['user'=>$user]);
         }
 
 
 
 
          public function user_edit($id){
            
             $user = User::find($id);
             
             //$user = Auth::user();
            
                
            
            
            
             return view('profile.user_edit',['user'=>$user]);
         }
         
 
 
 
         
         
          public function user_edit_submit(Request $req,$id){
            
            
             //$user = Auth::user();
            
               //dd($request);

            //$u = Auth::user();
            
            
            $User = User::find($id);
  
  //dd($id);

            $file = $req->file('avatar');
            
            if($file){

                if($User->avatar){
    
                    $image_path = public_path('images/avatars').'/'.$User->avatar;
                    
                    //echo $image_path;
          
                      if(File::exists($image_path)) {
                            File::delete($image_path);
                      }
                    
                }




            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('images/avatars'),$fileName); 

            
            }
            
            //dd($file);
            
    
            

            if(isset($fileName))
                $User->avatar = $fileName;

            $User->name = $req->input('login');
            
            if($req->input('password')){
                $password = Hash::make($req->input('password'));
                $User->password = $password;
            }
            $User->email = $req->input('email');
            
            $User->role_id = $req->input('role_id');

            $User->fullname = $req->input('fullname');
            $User->family = $req->input('family');
            $User->surname = $req->input('surname');
            $User->date = $req->input('date');
            $User->month = $req->input('month');
            $User->year = $req->input('year');
            
            if($req->input('sex_m')=='on'){ 
                $User->sex='m';
            }
            if($req->input('sex_w')=='on'){ 
                $User->sex='w';
            }
            //$Offer->lift = $req->input('lift') ? 1 : 0;
            //$Offer->parking = $req->input('parking') ? 1: 0;
            
            
            //$Offer->area = $req->input('area');
            //$Offer->rooms = $req->input('rooms');
            $User->country = $req->input('country');
            $User->region = $req->input('region');
            //$Offer->city = $req->input('city');
            $User->district = $req->input('district');
            //$Offer->street = $req->input('street');
            //$Offer->house = $req->input('house');
            
            $User->facebook = $req->input('facebook');
            $User->vk = $req->input('vk');
            $User->instagram = $req->input('instagram');

            $User->phone = $req->input('phone');
            $User->map_coordinates = $req->input('map_coordinates');

            //$Offer->furniture = $req->input('furniture') ? 1: 0;
            $User->status = $req->input('status');
            //$Offer->comment = $req->input('comment');
            
            
            
            //$Offer->user_id = $user->id;
            
            //$Offer->views = 0;
            
            $User->save();
            
/*            
            $images = $req->images;
            
            if($images){
                //while($i < count($req->input('images'))){
                foreach($images as $image){
                
                    $sql[] = array(
                      'offer_id' => $Offer->id,
                      'image_id' => $image,
                    );
                  //  $i++;
                }
                
                
                DB::table('offers_images')->insert($sql);
            }
 
 */
            
            
                        
           //return redirect()->route('admin.edit_profile');

            return redirect()->route('profile_user_edit',['user'=>$User]);
            
           //return view('profile.user_edit',['user'=>$User]); 
            
            
             //return view('admin.edit_profile',['user'=>$user]);
         }



         public function user_delete($id,$redirect=true)
         {

            //$user = Auth::user();
            
            $User = User::find($id);
            

            $filename = public_path('files').'/avatars/'.$User->avatar;
            
            //echo $image_path;
  
              if(File::exists($filename)) {
                    File::delete($filename);
              }


            DB::table('users')->where('id', '=', $id)->delete();
            DB::table('role_user')->where('user_id', '=', $id)->delete();


            if($redirect){
             return redirect()->route('profile_users');
            }

         }


          public function users_delete(Request $req){
            
            //$Ids = $req->delete;
            
            //dd($req);
            

                if($req->delete){
                
                    foreach($req->delete as $value){
                    
                        $this->user_delete($value,false);
                        
                    }
                
                }
                
                return redirect()->route('profile_users');
            

          }





//////////////////////



          public function employers_add(){
            
            
             return view('profile.employers_add');
         }



          public function employers_add_submit(Request $req){
            
            
            $User = new User();
  
  //dd($id);

            $file = $req->file('avatar');
            
            if($file){

                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('images/avatars'),$fileName); 

            
            }
            
            //dd($file);

            if(isset($fileName))
                $User->avatar = $fileName;



            $User->name = $req->input('login');
            
            if($req->input('password')){
                $password = Hash::make($req->input('password'));
                $User->password = $password;
            }
            $User->email = $req->input('email');
            
            $User->role_id = 3;
            


            $User->fullname = $req->input('fullname');
            $User->family = $req->input('family');
            $User->surname = $req->input('surname');
            $User->date = $req->input('date');
            $User->month = $req->input('month');
            $User->year = $req->input('year');
            
            if($req->input('sex_m')=='on'){ 
                $User->sex='m';
            }
            if($req->input('sex_w')=='on'){ 
                $User->sex='w';
            }
            //$Offer->lift = $req->input('lift') ? 1 : 0;
            //$Offer->parking = $req->input('parking') ? 1: 0;
            
            
            //$Offer->area = $req->input('area');
            //$Offer->rooms = $req->input('rooms');
            $User->country = $req->input('country');
            $User->region = $req->input('region');
            //$Offer->city = $req->input('city');
            $User->district = $req->input('district');
            //$Offer->street = $req->input('street');
            //$Offer->house = $req->input('house');
            
            $User->facebook = $req->input('facebook');
            $User->vk = $req->input('vk');
            $User->instagram = $req->input('instagram');

            $User->phone = $req->input('phone');
            $User->map_coordinates = $req->input('map_coordinates');

            //$Offer->furniture = $req->input('furniture') ? 1: 0;
            $User->status = $req->input('status');
            //$Offer->comment = $req->input('comment');
            
            
            
            //$Offer->user_id = $user->id;
            
            //$Offer->views = 0;
            
            $User->save();
            
            return redirect()->route('profile_employers_edit',['user'=>$User]);
            
           //return view('profile.employers_edit',['user'=>$User]); 
            
         }
 
 
 
 
          public function employers_edit($id){
            
             $User = User::find($id);
             
             //return redirect()->route('profile_employers_edit',['user'=>$User]);
             
            return view('profile.employers_edit',['user'=>$User]);
         }
         
 
 
 
         
         
          public function employers_edit_submit(Request $req,$id){
            
            $User = User::find($id);
  
            $file = $req->file('avatar');
            
            if($file){

                if($User->avatar){
    
                    $image_path = public_path('images/avatars').'/'.$User->avatar;
                    
                    //echo $image_path;
          
                      if(File::exists($image_path)) {
                            File::delete($image_path);
                      }
                    
                }




            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('images/avatars'),$fileName); 

            
            }
            
            //dd($file);
            
    
            

            if(isset($fileName))
                $User->avatar = $fileName;

            $User->name = $req->input('login');
            
            if($req->input('password')){
                $password = Hash::make($req->input('password'));
                $User->password = $password;
            }
            $User->email = $req->input('email');
            
            $User->role_id = $req->input('role_id');

            $User->fullname = $req->input('fullname');
            $User->family = $req->input('family');
            $User->surname = $req->input('surname');
            $User->date = $req->input('date');
            $User->month = $req->input('month');
            $User->year = $req->input('year');
            
            if($req->input('sex_m')=='on'){ 
                $User->sex='m';
            }
            if($req->input('sex_w')=='on'){ 
                $User->sex='w';
            }
            //$Offer->lift = $req->input('lift') ? 1 : 0;
            //$Offer->parking = $req->input('parking') ? 1: 0;
            
            
            //$Offer->area = $req->input('area');
            //$Offer->rooms = $req->input('rooms');
            $User->country = $req->input('country');
            $User->region = $req->input('region');
            //$Offer->city = $req->input('city');
            $User->district = $req->input('district');
            //$Offer->street = $req->input('street');
            //$Offer->house = $req->input('house');
            
            $User->facebook = $req->input('facebook');
            $User->vk = $req->input('vk');
            $User->instagram = $req->input('instagram');

            $User->phone = $req->input('phone');
            $User->map_coordinates = $req->input('map_coordinates');

            //$Offer->furniture = $req->input('furniture') ? 1: 0;
            $User->status = $req->input('status');
            //$Offer->comment = $req->input('comment');
            
            
            
            //$Offer->user_id = $user->id;
            
            //$Offer->views = 0;
            
            $User->save();
            
            
            return redirect()->route('profile_employers_edit',['user'=>$User]);
            
           //return view('admin.employers_edit',['user'=>$User]); 
            
         }



         public function employer_delete($id,$redirect=true)
         {

            //$user = Auth::user();
            
            $User = User::find($id);
            

            $filename = public_path('files').'/avatars/'.$User->avatar;
            
            //echo $image_path;
  
              if(File::exists($filename)) {
                    File::delete($filename);
              }


            DB::table('users')->where('id', '=', $id)->delete();

            if($redirect){
                return redirect()->route('profile_employers');
            }
            //return view('admin.users',['users' => $users,'roles' => $roles]);   

         }


         public function employers_delete(Request $req)
         {
         
                if($req->delete){
                
                    foreach($req->delete as $value){
                    
                        $this->employer_delete($value,false);
                        
                    }
                
                }
                
                return redirect()->route('profile_employers');         
         
         }   




    
}
