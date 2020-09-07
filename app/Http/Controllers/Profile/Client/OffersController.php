<?php

namespace App\Http\Controllers\Profile\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Model;

use App\Offers;

use App\User;

use Illuminate\Support\Facades\Auth;

use DB;

use File;

use Carbon\Carbon;
use App\Message;
use App\Reviews;

class OffersController extends Controller
{
    //
    
 
 
       public function request_edit($id){

            $user = Auth::user();
            
            $Offer = DB::table('offers')->where('id', $id)->first();

            $offer_files = DB::table('offers_files')
            ->where('offers_files.offer_id', $Offer->id)
            ->join('files', 'files.id', '=', 'offers_files.file_id')
            ->select('files.filename')
            ->get();
            
            $lawyers = DB::table('offers_lawyers')
                  ->select('users.*')
                  ->where('offers_lawyers.offer_id','=',$id)
                  ->join('users', 'users.id', '=', 'offers_lawyers.to_user_id')
                  ->get();
            
            //echo $Offer->user_id,$user->id;
            foreach($lawyers as $key=>$lawyer){

                $messages = DB::table('messages')
                ->where('offer_id', $id)
                ->whereIn('from_user_id', [$lawyer->id,$user->id])
                ->whereIn('to_user_id', [$lawyer->id,$user->id])
                ->get();
 
                $lawyers[$key]->messages_count=$messages->count();
                
            }

            return view('profile.client.request_edit', ['offer' => $Offer,'offer_files'=>$offer_files,'lawyers'=>$lawyers]);

        }



 
 
       public function request_rejected_submit($id,$id_m){


            $user = Auth::user();
            
            $offer_lawyer = DB::table('offers_lawyers')
            ->where('offer_id', $id)
            ->where('to_user_id', $id_m)
            ->first();

            $transaction = DB::table('offers_lawyers_transactions')
            ->where('offer_id', $id)
            ->where('offer_lawyer_id', $offer_lawyer->id)
            ->where('from_user_id', $user->id)
            ->where('to_user_id', $id_m)
            ->where('status', 'cancel')
            ->first();
            
            if($transaction){

                //$get_user = User::find($transaction->to_user_id);
                DB::table('users')
                ->where('id', $user->id)
                ->update(['amount'=>($user->amount + $transaction->summ)]);                 

                DB::table('offers_lawyers_transactions')
                ->where('id', $transaction->id)
                ->update(['status'=>'return','result_by'=>Carbon::now()]);                 


                DB::table('offers_lawyers')
                ->where('id', $transaction->offer_lawyer_id)
                ->update(['status'=>'rejected']);                 

                DB::table('offers')
                ->where('id', $transaction->offer_id)
                ->update(['status'=>'rejected']);  
               

                DB::table('users_transactions_report')->insert(
                ['user_id'=>$user->id,
                 'offer_id'=>$transaction->offer_id,
                 'summ'=>$transaction->summ,
                 'type_transaction'=>'+',
                 'message'=>'Возврат за заявку - '.$transaction->offer_id,
                 'created_at'=>Carbon::now(),
                 'updated_at'=>Carbon::now(),
                 ]);  


            }

            return redirect()->route('request_messages_offer_client',[$id,$id_m]);
            
       }  
 
 
 
 
 
    
    
         public function request_messages_offer($id,$id_m){

            $user = Auth::user();


            //$user = Auth::user();
            
            $Offer = DB::table('offers')->where('id', $id)->first();

            $offer_files = DB::table('offers_files')
            ->where('offers_files.offer_id', $Offer->id)
            ->join('files', 'files.id', '=', 'offers_files.file_id')
            ->select('files.filename')
            ->get();
            

            $offer_lawyer = DB::table('offers_lawyers')
            ->where('offer_id', $id)
            ->where('to_user_id', $id_m)->first();
            
            $offer_lawyer_transaction = DB::table('offers_lawyers_transactions')
            ->where('offer_id',$Offer->id)
            ->where('offer_lawyer_id',$offer_lawyer->id)
            ->where('from_user_id',$user->id)
            ->where('to_user_id',$id_m)
            ->first();
            
            //if(!$offer_lawyer_transaction) $offer_lawyer_transaction = [];
           
            $messages = DB::table('messages')
            ->where('offer_id', $id)
            ->whereIn('from_user_id', [$id_m,$user->id])
            ->whereIn('to_user_id', [$id_m,$user->id])
            ->get();

            $review = DB::table('reviews')
            ->where('offer_id', $id)
            ->where('from_user_id', $user->id)
            ->where('to_user_id', $id_m)
            ->first();
            
                //print_r($review);
            //if($messages->count()){

                return view('profile.client.request_messages', ['offer' => $Offer,'offer_lawyer'=>$offer_lawyer,'offer_lawyer_transaction'=>$offer_lawyer_transaction,'offer_files'=>$offer_files,'messages' => $messages,'review'=>$review,'id_m'=>$id_m,'user_id'=>$user->id]);        
                
            //}else{
                
            //    return redirect()->route('request_client',[$id]);
                
            //}

         }     
    

       public function request_review_user_submit(Request $req,$id,$id_m){


                $user = Auth::user();
            
                $Offer = Offers::find($id);              

   
                $Review = new Reviews();
                $Review->offer_id = $Offer->id;
                $Review->message = $req->input('RequestDetails');
                $Review->rating = $req->input('rating');
    
                $Review->from_user_id = $user->id;
                $Review->to_user_id = $id_m;

                $Review->created_at = Carbon::now();
                $Review->updated_at = Carbon::now();
                
                $Review->save();

                $get_user = User::find($id_m);
                DB::table('users')
                ->where('id', $get_user->id)
                ->update(['vote'=>($get_user->vote + 1),'rating'=>($get_user->rating + $req->input('rating'))]); 


    
                return redirect()->route('request_messages_offer_client',[$id,$id_m]);
                
       }     



       public function request_answer_user_submit(Request $req,$id,$id_m){


                $user = Auth::user();
            
                $Offer = Offers::find($id);              
    
                $Message = new Message();
                $Message->offer_id = $Offer->id;
                $Message->message = $req->input('RequestDetails');
    
                $Message->from_user_id = $user->id;
                $Message->to_user_id = $id_m;

                $Message->created_at = Carbon::now();
                $Message->updated_at = Carbon::now();
                
                $Message->save();
    
                return redirect()->route('request_messages_offer_client',[$id,$id_m]);
                
       }     

    
       public function request_answer_submit(Request $req,$id){


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
    
                $Message->from_user_id = $user->id;
                $Message->to_user_id = $Offer->user_id;

                $Message->created_at = Carbon::now();
                $Message->updated_at = Carbon::now();

                $Message->save();
    
                return redirect()->route('request_answer',[$id]);
            
            }else{
                return redirect()->route('profile_index');
                
            }            


       } 

       public function request_answer($id){
        
/*          $user = Auth::user();
            
            //$result = '';
            $Offer = DB::table('offers')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();
            
*/
            return view('profile.request_answer', ['offer_id' => $id]);
            
            //return view('profile.request_answer');
           
            //return response()->json(['data'=>$result]);

       }

       public function request_status(Request $req,$id){
        
            $user = Auth::user();
            
            $result = '';
            
            DB::table('offers')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->update(['status'=>$req->status]);            


/*            DB::table('offers_lawyers')
            ->where('offer_id', $id)
            ->where('to_user_id', $id_m)
            ->update(['status'=>$req->status]);            
*/
            return response()->json(['data'=>$req->status]);

       }

       public function request_pay($id,$id_m){


            $user = Auth::user();
            
            $Offer = DB::table('offers')->where('id', $id)->first();


            $offer_lawyer = DB::table('offers_lawyers')
            ->where('offer_id', $id)
            ->where('to_user_id', $id_m)->first();


            $offer_lawyer_transaction = DB::table('offers_lawyers_transactions')
            ->where('offer_id',$Offer->id)
            ->where('offer_lawyer_id',$offer_lawyer->id)
            ->where('from_user_id',$user->id)
            ->where('to_user_id',$id_m)
            ->first();
                
           
            return view('profile.client.request_pay', ['offer' => $Offer,'offer_lawyer'=>$offer_lawyer,'offer_lawyer_transaction'=>$offer_lawyer_transaction,'id_m'=>$id_m,'message'=>'']);        
 
       } 
       
       public function request_pay_submit($id,$id_m){


            $user = Auth::user();

            $Offer = DB::table('offers')->where('id', $id)->first();
            
            $message = '';
            

            $offer_lawyer = DB::table('offers_lawyers')
            ->where('offer_id', $id)
            ->where('to_user_id', $id_m)->first();
                
            if($offer_lawyer){
            
                if(Auth::user()->amount < $offer_lawyer->cost){
                    
                    $message = 'Недостаточно средств на счету';

                }else{

    
                    DB::table('offers_lawyers')
                    ->where('id', $offer_lawyer->id)
                    ->update(['status'=>'processing']);                 
    
                    DB::table('offers')
                    ->where('id', $offer_lawyer->offer_id)
                    ->update(['status'=>'processing']);   
                
                    DB::table('users')
                    ->where('id', $user->id)
                    ->update(['amount'=>(Auth::user()->amount - $offer_lawyer->cost)]);                 
                    
                    DB::table('offers_lawyers_transactions')->insert(
                    ['offer_id'=>$Offer->id,
                     'offer_lawyer_id'=>$offer_lawyer->id,
                     'from_user_id'=>$user->id,
                     'to_user_id'=>$id_m,
                     'summ'=>$offer_lawyer->cost,
                     'status'=>'waiting',
                     'created_by'=>Carbon::now(),
                     //'result_by'=>'waiting'
                     ]);  
                     
                     
                    DB::table('users_transactions_report')->insert(
                    ['user_id'=>$user->id,
                     'offer_id'=>$Offer->id,
                     'summ'=>$offer_lawyer->cost,
                     'type_transaction'=>'-',
                     'message'=>'Оплата заявки - '.$Offer->id,
                     'created_at'=>Carbon::now(),
                     'updated_at'=>Carbon::now(),
                     ]);  


                     
                     
                     
                    $message = 'Оплата прошла успешно';
                    
                }
            
            }
            
            
            return redirect()->route('request_pay_client',[$id,$id_m]);

       }        
       

       public function request_payment_submit($id,$id_m){


            $user = Auth::user();
            
            $offer_lawyer = DB::table('offers_lawyers')
            ->where('offer_id', $id)
            ->where('to_user_id', $id_m)
            ->first();

            $transaction = DB::table('offers_lawyers_transactions')
            ->where('offer_id', $id)
            ->where('offer_lawyer_id', $offer_lawyer->id)
            ->where('from_user_id', $user->id)
            ->where('to_user_id', $id_m)
            ->where('status', 'waiting')
            ->first();
            
            if($transaction){
                $get_user = User::find($transaction->to_user_id);
                DB::table('users')
                ->where('id', $get_user->id)
                ->update(['amount'=>($get_user->amount + $transaction->summ)]);                 

                DB::table('offers_lawyers_transactions')
                ->where('id', $transaction->id)
                ->update(['status'=>'payment','result_by'=>Carbon::now()]);                 

                DB::table('offers_lawyers')
                ->where('id', $transaction->offer_lawyer_id)
                ->update(['status'=>'completed']);                 

                DB::table('offers')
                ->where('id', $transaction->offer_id)
                ->update(['status'=>'completed']);                 

                DB::table('users_transactions_report')->insert(
                ['user_id'=>$get_user->id,
                 'offer_id'=>$transaction->offer_id,
                 'summ'=>$transaction->summ,
                 'type_transaction'=>'+',
                 'message'=>'Оплата заявки - '.$transaction->offer_id,
                 'created_at'=>Carbon::now(),
                 'updated_at'=>Carbon::now(),
                 ]);  


            }

            return redirect()->route('request_messages_offer_client',[$id,$id_m]);
            
       }      
       



       public function request($id){

            $user = Auth::user();
            
            $Offer = DB::table('offers')->where('id', $id)->first();

            $offer_files = DB::table('offers_files')
            ->where('offers_files.offer_id', $Offer->id)
            ->join('files', 'files.id', '=', 'offers_files.file_id')
            ->select('files.filename')
            ->get();
            
            $lawyers = DB::table('offers_lawyers')
                  ->select('users.*')
                  ->where('offers_lawyers.offer_id','=',$id)
                  ->join('users', 'users.id', '=', 'offers_lawyers.to_user_id')
                  ->get();
            
            //echo $Offer->user_id,$user->id;
            foreach($lawyers as $key=>$lawyer){

                $messages = DB::table('messages')
                ->where('offer_id', $id)
                ->whereIn('from_user_id', [$lawyer->id,$user->id])
                ->whereIn('to_user_id', [$lawyer->id,$user->id])
                ->get();
 
                $lawyers[$key]->messages_count=$messages->count();
                
            }

            return view('profile.client.request', ['offer' => $Offer,'offer_files'=>$offer_files,'lawyers'=>$lawyers]);

        }

         public function offers(){
        
//$offers = App\Offers::all();
//return view('profile.offers', ['offers' => $offers]); 
            
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
