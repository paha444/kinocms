<?php

namespace App\Http\Controllers\Profile\Lawyer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Model;

use App\Offers;

use Illuminate\Support\Facades\Auth;

use DB;

use File;

use Carbon\Carbon;
use App\Message;

class OffersController extends Controller
{
    //


       public function request_cancel_submit(Request $req,$id){


                $user = Auth::user();
                
                //$Offer = Offers::find($id); 
                //if(!$Offer) die;
                
                $offer_lawyer = DB::table('offers_lawyers')
                    ->where('offer_id', $id)
                    ->where('to_user_id', $user->id)
                    ->first();
    
                $transaction = DB::table('offers_lawyers_transactions')
                ->where('offer_id', $id)
                ->where('offer_lawyer_id', $offer_lawyer->id)
                //->where('from_user_id', $user->id)
                ->where('to_user_id', $user->id)
                ->where('status', 'waiting')
                ->first();
            
                if($transaction){
                   

                    DB::table('offers_lawyers')
                    ->where('id', $offer_lawyer->id)
                    //->where('user_id', $user->id)
                    ->update(['status'=>'cancel']);  


                    DB::table('offers_lawyers_transactions')
                    ->where('id', $transaction->id)
                    ->update(['status'=>'cancel','result_by'=>Carbon::now()]); 
                    
                }
                
                
                return redirect()->route('request_lawyer',[$id]);
                    
         }    
        
    
         public function request_messages_offer($id,$id_m){

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
            
                //return view('profile.request_answer', ['offer_id' => $id]);
            
                return view('profile.lawyer.request_messages', ['offers' => $offers,'messages' => $messages,'offer_id'=>$id,'id_m'=>$id_m,'user_id'=>$user->id]);        
    
            }else{
                
                return redirect()->route('messages');
                
            }

            // return view('admin.offers');
         }     
    
    
    
    


       public function request_answer_submit(Request $req,$id){


                $user = Auth::user();
                
                $Offer = Offers::find($id); 
                if(!$Offer) die;
                
                $Offer_check = DB::table('offers_lawyers')
                    ->where('offer_id', $id)
                    ->where('to_user_id', $user->id)
                    ->first();
                
                if($Offer_check){
                    
                    $cost = $req->input('Cost');
                    $time = $req->input('Time');
                    
                    
                    DB::table('offers_lawyers')
                    ->where('id', $Offer_check->id)
                    //->where('user_id', $user->id)
                    ->update(['cost'=>$cost,'time'=>$time]);                 
                    
                }else{
                    
                    $cost = $req->input('Cost');
                    $time = $req->input('Time');
                    DB::table('offers_lawyers')->insert(['offer_id'=>$id,'to_user_id'=>$user->id,'cost'=>$cost,'time'=>$time,'status'=>'new']);
                    
                    
                    if($req->input('RequestDetails')){
                    
                        $Message = new Message();
                        $Message->offer_id = $id;
                        $Message->message = $req->input('RequestDetails');
            
                        $Message->from_user_id = $user->id;
                        $Message->to_user_id = $Offer->user_id;
        
                        $Message->created_at = Carbon::now();
                        $Message->updated_at = Carbon::now();
                        
                        $Message->save();
                    
                    }
                    
                }
                
                return redirect()->route('request_lawyer',[$id]);
                

       }     
    
   
    
       public function request_send_message_submit(Request $req,$id){


            $user = Auth::user();
            
            $Offer_check = DB::table('offers_lawyers')
                ->where('offer_id', $id)
                ->where('to_user_id', $user->id)
                ->first();
    
            if($Offer_check){
            
                $Offer = Offers::find($id);              
    
    
                $Message = new Message();
                $Message->offer_id = $Offer->id;
                $Message->message = $req->input('RequestDetails');
    
                //$Message->cost = $req->input('Cost');
                //$Message->time = $req->input('Time');
    
    
                $Message->from_user_id = $user->id;
                $Message->to_user_id = $Offer->user_id;

                $Message->created_at = Carbon::now();
                $Message->updated_at = Carbon::now();
                
                
                
                
                $Message->save();
    
                
                return redirect()->route('request_lawyer',[$id]);
                
            
            }else{
                return redirect()->route('profile_index_lawyer');
                
            }            


       } 

       public function request_answer($id){
        
            return view('profile.lawyer.request_answer', ['offer_id' => $id]);
            
       }

       public function request_status(Request $req,$id){
        
            $user = Auth::user();
            
           
            DB::table('offers_lawyers')->insert(['offer_id'=>$id,'to_user_id'=>$user->id]);

            DB::table('offers')
                    ->where('id', $id)
                    ->update(['status'=>$req->status]);            
            
            $result = 'ok';
            
           
            return response()->json(['data'=>$result]);

       }

       public function request($id){
            
            $user = Auth::user();
            
            $offer_lawyer_transaction = '';
            
            
            $Offer = DB::table('offers')
            ->where('id', $id)
            //->where('user_id', $user->id)
            ->first();
            
            if($Offer){

            $offer_lawyer = DB::table('offers_lawyers')
            ->where('offer_id', $id)
            ->where('to_user_id', $user->id)->first();

            
                
            $offer_files = DB::table('offers_files')
            ->where('offers_files.offer_id', $Offer->id)
            ->join('files', 'files.id', '=', 'offers_files.file_id')
            ->select('files.filename')
            ->get();
            
          
            $messages = DB::table('messages')
            ->where('offer_id', $id)
            ->whereIn('from_user_id', [$Offer->user_id,$user->id])
            ->whereIn('to_user_id', [$Offer->user_id,$user->id])
            ->get();
            
////////////////////

            $review = DB::table('reviews')
            ->where('offer_id', $id)
            ->where('to_user_id', $user->id)
            ->first();


            
            if($offer_lawyer){
            
                $offer_lawyer_transaction = DB::table('offers_lawyers_transactions')
                ->where('offer_id', $id)
                ->where('offer_lawyer_id', $offer_lawyer->id)
                ->where('from_user_id', $Offer->user_id)
                ->where('to_user_id', $user->id)->first();
            
            }
            
            return view('profile.lawyer.request', ['offer' => $Offer,'offer_lawyer'=>$offer_lawyer,'offer_lawyer_transaction'=>$offer_lawyer_transaction,'offer_files'=>$offer_files,'messages'=>$messages,'review'=>$review,'user_id'=>$user->id]);
            
            }else{
                
                return redirect()->route('profile_index_lawyer');
                
            }
            
        }

         public function offers(){
        
            
            $user = Auth::user();

            $offers = DB::table('offers')
            ->where('user_id', $user->id)
            ->join('users', 'users.id', '=', 'offers.user_id')
            
            ->select('offers.*','users.name')
            ->get();
            
            
        
            return view('profile.offers', ['offers' => $offers]);        

            // return view('profile.offers');
         }

    
    
         public function submit(Request $req)
         {
            
            
            
            $user = Auth::user();
            
            //dd($req);
            
            
            
            $Offer = new Offers();

            

            $file = $req->file('file');
            
            if($file){
            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('files'),$fileName); 

            
            }            


            $Offer->type_offer = $req->input('type_offer');
            $Offer->type_offer_period = $req->input('type_offer_period');
            $Offer->price = $req->input('price');
            $Offer->type_object = $req->input('type_object');
            $Offer->number_floors = $req->input('number_floors');
            $Offer->floor = $req->input('floor');
            
            //if($req->input('lift')=='on') $lift = 1 else $lift = 0;
            $Offer->new_building = $req->input('new_building') ? 1 : 0;
            $Offer->buy_mortgage = $req->input('buy_mortgage') ? 1 : 0;


            $Offer->building_material = $req->input('building_material');
            $Offer->renovation = $req->input('renovation') ? 1 : 0;
            $Offer->type_sale = $req->input('type_sale');
            $Offer->distance_metro_p = $req->input('distance_metro_p');
            $Offer->distance_metro_t = $req->input('distance_metro_t');
            $Offer->area_kitchen = $req->input('area_kitchen');
            $Offer->isolated_rooms = $req->input('isolated_rooms') ? 1 : 0;
            $Offer->apartments = $req->input('apartments');
            $Offer->ceiling_height = $req->input('ceiling_height');
            $Offer->year_built = $req->input('year_built');
            $Offer->repair = $req->input('repair');
            $Offer->separate_bathroom = $req->input('separate_bathroom') ? 1 : 0;
            $Offer->balcony = $req->input('balcony');
            $Offer->lift = $req->input('lift');


            
            //$Offer->lift = $req->input('lift') ? 1 : 0;
            $Offer->parking = $req->input('parking') ? 1: 0;
            
            
            $Offer->area = $req->input('area');
            $Offer->rooms = $req->input('rooms');
            $Offer->country = $req->input('country');
            $Offer->region = $req->input('region');
            $Offer->city = $req->input('city');
            $Offer->district = $req->input('district');
            $Offer->street = $req->input('street');
            $Offer->house = $req->input('house');
            $Offer->map_coordinates = $req->input('map_coordinates');
            
            $Offer->furniture = $req->input('furniture') ? 1: 0;
            $Offer->status = $req->input('status');
            $Offer->comment = $req->input('comment');

            if(isset($fileName))
                $Offer->filename = $fileName;
                            
            $Offer->user_id = $user->id;
            
            $Offer->views = 0;
            
            $Offer->save();
            
            
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
            
            
            
            
            
            
                        
            return redirect()->route('profile_offers_edit',$Offer->id);
             
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
            
    
            $Offer = Offers::find($id);

//$Offer->comment = '1111111';

//$Offer->save();
            
           
            //$Offer = new Offers();
            


            $Offer->type_offer = $req->input('type_offer');
            $Offer->type_offer_period = $req->input('type_offer_period');
            $Offer->price = $req->input('price');
            $Offer->type_object = $req->input('type_object');
            $Offer->number_floors = $req->input('number_floors');
            $Offer->floor = $req->input('floor');
            
            //if($req->input('lift')=='on') $lift = 1 else $lift = 0;
            $Offer->new_building = $req->input('new_building') ? 1 : 0;
            $Offer->buy_mortgage = $req->input('buy_mortgage') ? 1 : 0;

            $Offer->building_material = $req->input('building_material');
            $Offer->renovation = $req->input('renovation') ? 1 : 0;
            $Offer->type_sale = $req->input('type_sale');
            $Offer->distance_metro_p = $req->input('distance_metro_p');
            $Offer->distance_metro_t = $req->input('distance_metro_t');
            $Offer->area_kitchen = $req->input('area_kitchen');
            $Offer->isolated_rooms = $req->input('isolated_rooms') ? 1 : 0;
            $Offer->apartments = $req->input('apartments');
            $Offer->ceiling_height = $req->input('ceiling_height');
            $Offer->year_built = $req->input('year_built');
            $Offer->repair = $req->input('repair');
            $Offer->separate_bathroom = $req->input('separate_bathroom') ? 1 : 0;
            $Offer->balcony = $req->input('balcony');
            $Offer->lift = $req->input('lift');

            
            //$Offer->lift = $req->input('lift') ? 1 : 0;
            $Offer->parking = $req->input('parking') ? 1: 0;
            
            
            $Offer->area = $req->input('area');
            $Offer->rooms = $req->input('rooms');
            $Offer->country = $req->input('country');
            $Offer->region = $req->input('region');
            $Offer->city = $req->input('city');
            $Offer->district = $req->input('district');
            $Offer->street = $req->input('street');
            $Offer->house = $req->input('house');
            $Offer->map_coordinates = $req->input('map_coordinates');
            $Offer->furniture = $req->input('furniture') ? 1: 0;
            $Offer->status = $req->input('status');
            $Offer->comment = $req->input('comment');
            
            if(isset($fileName))
                $Offer->filename = $fileName;
            
            
            $Offer->user_id = $user->id;
            
            $Offer->views = 0;
            
            $Offer->save();
            
            
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
 
 
            
            
                        
           return redirect()->route('profile_offers_edit',$id);
             
         }         
         
         
         public function edit($id)
         {
            
            $user = Auth::user();
            
            $Offer = DB::table('offers')->where('id', $id)->where('user_id', $user->id)->first();
            
            $offer_images = DB::table('offers_images')
            ->where('offers_images.offer_id', $Offer->id)
            ->join('images', 'images.id', '=', 'offers_images.image_id')
            ->select('images.filename')
            ->get();
                        
            //dd($Offer);
            //dd($offers_images);
            
            return view('profile.offers_edit', ['offer' => $Offer,'offer_images'=>$offer_images]);     


         }


         public function offer_delete($id,$redirect=true)
         {
            
            $user = Auth::user();
            
            $Offer = DB::table('offers')
            ->where('id', $id)
            //->where('user_id', $user->id)
            ->first();


            $filename = public_path('files').'/'.$Offer->filename;
            
            //echo $image_path;
  
              if(File::exists($filename)) {
                    File::delete($filename);
              }

            $offer_images = DB::table('offers_images')
            ->where('offers_images.offer_id', $Offer->id)
            ->join('images', 'images.id', '=', 'offers_images.image_id')
            ->select('images.filename')
            ->get();


            foreach($offer_images as $image){
                
                DB::table('images')->where('filename', '=', $image->filename)->delete();

                
                $filename = public_path('images').'/'.$image->filename;
                
                  if(File::exists($filename)) {
                        File::delete($filename);
                  }
                
            }


            DB::table('offers_images')->where('offer_id', '=', $Offer->id)->delete();


            DB::table('offers')->where('id', '=', $Offer->id)->delete();

   
             
             if($redirect){
                return redirect()->route('profile_offers');
             //}else{
                
             }

         }


         public function offers_delete(Request $req)
         {
                
                if($req->delete){
                
                    foreach($req->delete as $value){
                    
                        $this->offer_delete($value,false);
                        
                    }
                
                }
                
                return redirect()->route('profile_offers');
                
                //dd($req);
                
         }


}
