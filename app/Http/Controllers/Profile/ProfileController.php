<?php

namespace App\Http\Controllers\Profile;

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

class ProfileController extends Controller
{
    
    
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
                     
                return redirect()->route('profile_payment',$payment_id);
         }
         
         
             
         public function profile(){
            //return view('profile.dashboard');
        
            if(Auth::user()->isClient()){

                return redirect()->route('profile_index_client');
                //->with('message', 'Ваша заявка успешно отправленна, ожидайте ответа.');

            }elseif(Auth::user()->isBusiness() or Auth::user()->isLawyers()){

                return redirect()->route('profile_index_lawyer');

            }
            
            

            //->with('message', 'Ваша заявка успешно отправленна, ожидайте ответа.');
    
        
/*            
            $user = Auth::user();

            $offers = DB::table('offers')
            //->where('user_id', $user->id)
            ->join('offers_lawyers', 'offers_lawyers.offer_id', '=', 'offers.id')
            
            ->select('offers.*')
            ->groupBy('offers.id')
            ->get();
            

             return view('profile.profile',['user'=>$user,'offers' => $offers]);
*/

         }


         public function add_money(){
             return view('profile.add_money');
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
                
                return redirect()->route('profile_payment',$id);
            
            }else{
                
                return redirect()->route('profile_add_money');
                
            }

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
                
                    return view('profile.payment',['payment'=>$payment]);
    
                }else{
                    
                    return redirect()->route('profile_add_money');
                    
                }

            
            }else{
                
            return redirect()->route('profile_add_money');
                
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
            
                
            
            
            
             return view('profile.edit_profile',['user'=>$user]);
         }
        
         
         
         
          public function edit_profile_submit(Request $req){
            
            
             //$user = Auth::user();
            
               //dd($request);

            $u = Auth::user();
            
  
  
  //dd($id);

            $file = $req->file('avatar');
            
            if($file){

                if($u->avatar){
    
                    $image_path = public_path('images/avatars').'/'.$u->avatar;
                    
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
            
    
            $User = User::find($u->id);

            if(isset($fileName))
                $User->avatar = $fileName;

            $User->name = $req->input('login');
            
            if($req->input('password')){
                $password = Hash::make($req->input('password'));
                $User->password = $password;
            }
            $User->email = $req->input('email');


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
            //$User->map_coordinates = $req->input('map_coordinates');

            //$Offer->furniture = $req->input('furniture') ? 1: 0;
            //$Offer->status = $req->input('status');
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
            
            
                        
           return redirect()->route('profile_edit_profile');



            
            
            
             //return view('admin.edit_profile',['user'=>$user]);
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
