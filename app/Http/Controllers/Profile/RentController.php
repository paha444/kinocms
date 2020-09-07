<?php

namespace App\Http\Controllers\Profile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Rents;

use Illuminate\Support\Facades\Auth;

use File;

use DB;

class RentController extends Controller
{
    //


         public function rental_request(){
        
//$offers = App\Offers::all();
//return view('admin.offers', ['offers' => $offers]); 
            $user = Auth::user();

            $rents = DB::table('rents')
            ->where('user_id', $user->id)
            ->join('users', 'users.id', '=', 'rents.user_id')
            ->select('rents.*','users.name')
            ->get();
            
            
        
            return view('profile.rental_request', ['rents' => $rents]);        

            // return view('admin.offers');
         }
    
        // public function rental_request(){
        //  //  return view('admin.rental_request');
        // }
    
    
    
         public function rental_request_add(Request $request){

            $result = $request->session()->all();//получаем данные из сессии
    	    $token = $result['_token'];


             return view('profile.rental_request_add',['token'=>$token]);
         }
    
    
         public function submit(Request $req)
         {
            
            //dd($req);
            
            
            $user = Auth::user();
            
            //dd($req);
            
            
            
            $Rent = new Rents();

            

            $file = $req->file('file');
            
            if($file){
            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('files'),$fileName); 

            
            }             
            
            
            
            $Rent->type_rent = $req->input('type_rent');
            $Rent->type_rent_period = $req->input('type_rent_period');
            $Rent->price = $req->input('price');
            
            //$Rent->type_rent_date_from = $req->input('type_rent_date_from');
            //$Rent->type_rent_date_to = $req->input('type_rent_date_to');
            

            $type_rent_date_from = null;
            $type_rent_date_to = null;
            //die;
            if($req->input('type_rent_date_from')!=''){
                $date_from = explode('/', $req->input('type_rent_date_from'));
                    $type_rent_date_from = date("Y.m.d", strtotime($date_from[2]."-".$date_from[1]."-".$date_from[0]));
            }
            
            if($req->input('type_rent_date_to')!=''){
                $date_to = explode('/', $req->input('type_rent_date_to'));
                $type_rent_date_to = date("Y.m.d", strtotime($date_to[2]."-".$date_to[1]."-".$date_to[0]));//date("yyyy.mm.dd", $date_to);
            }
            $Rent->type_rent_date_from = $type_rent_date_from;
            $Rent->type_rent_date_to = $type_rent_date_to;

            
            
            $Rent->type_object = $req->input('type_object');
            $Rent->number_floors = $req->input('number_floors');
            $Rent->floor = $req->input('floor');
            
            //if($req->input('lift')=='on') $lift = 1 else $lift = 0;
            $Rent->lift = $req->input('lift') ? 1 : 0;
            $Rent->parking = $req->input('parking') ? 1: 0;
            
            
            $Rent->area = $req->input('area');
            $Rent->rooms = $req->input('rooms');
            $Rent->country = $req->input('country');
            $Rent->region = $req->input('region');
            $Rent->city = $req->input('city');
            $Rent->district = $req->input('district');
            $Rent->street = $req->input('street');
            $Rent->house = $req->input('house');
            $Rent->map_coordinates = $req->input('map_coordinates');
            
            $Rent->furniture = $req->input('furniture') ? 1: 0;
            $Rent->status = $req->input('status');
            $Rent->comment = $req->input('comment');

            if(isset($fileName))
                $Rent->filename = $fileName;
                            
            $Rent->user_id = $user->id;
            
            $Rent->views = 0;
            
            $Rent->save();
            
            
            
            return redirect()->route('profile_rental_request',$Rent->id);
            
         }
    





         public function submit_edit(Request $req,$id)
         {
            
            $user = Auth::user();
  
  
  //dd($id);

            $file = $req->file('file');
            
            if($file){
            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('files'),$fileName); 

            
            }
            
            //dd($file);
            

            $Rent = Rents::find($id);

            $Rent->type_rent = $req->input('type_rent');
            $Rent->type_rent_period = $req->input('type_rent_period');
            $Rent->price = $req->input('price');
            
            //$Rent->type_rent_date_from = $req->input('type_rent_date_from');
            //$Rent->type_rent_date_to = $req->input('type_rent_date_to');
            
            $type_rent_date_from = null;
            $type_rent_date_to = null;
            //die;
            if($req->input('type_rent_date_from')!=''){
                $date_from = explode('/', $req->input('type_rent_date_from'));
                    $type_rent_date_from = date("Y.m.d", strtotime($date_from[2]."-".$date_from[1]."-".$date_from[0]));
            }
            
            if($req->input('type_rent_date_to')!=''){
                $date_to = explode('/', $req->input('type_rent_date_to'));
                $type_rent_date_to = date("Y.m.d", strtotime($date_to[2]."-".$date_to[1]."-".$date_to[0]));//date("yyyy.mm.dd", $date_to);
            }
            $Rent->type_rent_date_from = $type_rent_date_from;
            $Rent->type_rent_date_to = $type_rent_date_to;
            
            
            $Rent->type_object = $req->input('type_object');
            $Rent->number_floors = $req->input('number_floors');
            $Rent->floor = $req->input('floor');
            
            //if($req->input('lift')=='on') $lift = 1 else $lift = 0;
            $Rent->lift = $req->input('lift') ? 1 : 0;
            $Rent->parking = $req->input('parking') ? 1: 0;
            
            
            $Rent->area = $req->input('area');
            $Rent->rooms = $req->input('rooms');
            $Rent->country = $req->input('country');
            $Rent->region = $req->input('region');
            $Rent->city = $req->input('city');
            $Rent->district = $req->input('district');
            $Rent->street = $req->input('street');
            $Rent->house = $req->input('house');
            $Rent->map_coordinates = $req->input('map_coordinates');
            
            $Rent->furniture = $req->input('furniture') ? 1: 0;
            $Rent->status = $req->input('status');
            $Rent->comment = $req->input('comment');
            
            
            if(isset($fileName))
                $Rent->filename = $fileName;
            
            
            //$Offer->user_id = $user->id;
            
            //$Offer->views = 0;
            
            $Rent->save();
            
            
/*            $images = $req->images;
            
            if($images){
                //while($i < count($req->input('images'))){
                foreach($images as $image){
                
                    $sql[] = array(
                      'offer_id' => $Offer->id,
                      'image_id' => $image,
                    );
                  //  $i++;
                }
                
                
                //DB::table('offers_images')->insert($sql);
            }
 */
 
            
            
                        
           return redirect()->route('profile_rent_edit',$id);
             
         } 






         public function edit($id)
         {
            
            $user = Auth::user();
            
            $rent = DB::table('rents')->where('id', $id)->where('user_id', $user->id)->first();
            
/*            $offer_images = DB::table('offers_images')
            ->where('offers_images.offer_id', $Offer->id)
            ->join('images', 'images.id', '=', 'offers_images.image_id')
            ->select('images.filename')
            ->get();
                        
*/            //dd($Offer);
            //dd($offers_images);
            
            return view('profile.rental_request_edit', ['rent' => $rent]);     


         }



        public function rent_delete_file(Request $request)
        {
            
            
            $Rents = Rents::where('filename', $request->filetodelete)->first();
            //$Offer = $Offer->where('image', $path);
            $Rents->filename = '';
            $Rents->save();
                        
            $image_path = public_path('files').'/'.$request->filetodelete;
            
            //echo $image_path;
  
              if(File::exists($image_path)) {
                    File::delete($image_path);
              }
            
        
        }



         public function delete($id,$redirect=true)
         {
            
            $user = Auth::user();
            
            $rent = DB::table('rents')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();


            $filename = public_path('files').'/'.$rent->filename;
            
            //echo $image_path;
  
              if(File::exists($filename)) {
                    File::delete($filename);
              }

            DB::table('rents')->where('id', '=', $rent->id)->delete();


             //return redirect()->route('profile_rental_request');
            

             if($redirect){
                return redirect()->route('profile_rental_request');
             }            

         }


         public function rental_delete(Request $req)
         {

                if($req->delete){
                
                    foreach($req->delete as $value){
                    
                        $this->delete($value,false);
                        
                    }
                
                }
                
                return redirect()->route('profile_rental_request');

         }      
         
         
         
         
         

}
