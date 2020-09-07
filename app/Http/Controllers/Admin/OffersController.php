<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Model;

use App\Offers;

use Illuminate\Support\Facades\Auth;

use DB;

use File;

class OffersController extends Controller
{


         public function offers(){
        
//$offers = App\Offers::all();
//return view('admin.offers', ['offers' => $offers]); 
            $user = Auth::user();

            $offers = DB::table('offers')
            //->where('user_id', $user->id)
            ->leftjoin('users', 'users.id', '=', 'offers.user_id')
            ->select('offers.*','users.name')
            //->get();
            ->orderByDesc('offers.id')
            ->paginate(20);
            
            
        
            return view('admin.offers', ['offers' => $offers]);        

            // return view('admin.offers');
         }

    
    
         public function submit(Request $req)
         {
            
            
            
            $user = Auth::user();
            
            //dd($req);
            
            
            
            $Offer = new Offers();

            

            $file = $req->file('file');
            
            if(isset($file)){
            
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
/*            
            $table->enum('building_material', ['monolith', 'brick', 'panel'])->nullable();
            $table->boolean('renovation')->default(0)->unsigned();
            $table->enum('type_sale', ['free', 'alternative'])->nullable();
            $table->string('distance_metro_p')->nullable();
            $table->string('distance_metro_t')->nullable();
            $table->integer('area_kitchen')->nullable()->unsigned();
            $table->boolean('isolated_rooms')->default(0)->unsigned();
            $table->enum('apartments', ['yes', 'no'])->nullable();
            $table->integer('ceiling_height')->nullable()->unsigned();
            $table->integer('year_built')->nullable()->unsigned();
            $table->enum('repair', ['elite', 'euro', 'standard', 'no repair'])->nullable();
            $table->boolean('separate_bathroom')->default(0)->unsigned();
            $table->enum('balcony', ['yes', 'no', 'several'])->nullable();
            $table->enum('lift', ['yes', 'no', 'cargo'])->nullable();
*/            
            
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
            
            
            
            
            
            
                        
            return redirect()->route('offers_edit',$Offer->id);
             
         }
         


         public function submit_edit(Request $req,$id)
         {
            
            $user = Auth::user();
  
  
  //dd($id);
/*
            $file = $req->file('file');
            
            if($file){
            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('files'),$fileName); 

            
            }
            */
            //dd($file);
            
    
            $Offer = Offers::find($id);

//$Offer->comment = '1111111';

//$Offer->save();
            
           
            //$Offer = new Offers();
            


            $Offer->Rubric = $req->input('Rubric');
            $Offer->type_offer_period = $req->input('RequestCaption');
            $Offer->price = $req->input('RequestDetails');
/*
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
                $Offer->filename = $fileName;*/
            
            
            //$Offer->user_id = $user->id;
            
            //$Offer->views = 0;
            
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
 
 
            
            
                        
           return redirect()->route('offers_edit',$id);
             
         }         
         
         


         public function edit($id)
         {
            
            $user = Auth::user();
            
            $Offer = DB::table('offers')->where('id', $id)
            //->where('user_id', $user->id)
            ->first();
            
            $offer_images = DB::table('offers_images')
            ->where('offers_images.offer_id', $Offer->id)
            ->join('images', 'images.id', '=', 'offers_images.image_id')
            ->select('images.filename')
            ->get();
                        
            //dd($Offer);
            //dd($offers_images);
            
            return view('admin.offers_edit', ['offer' => $Offer,'offer_images'=>$offer_images]);     


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
                return redirect()->route('offers');
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
                
                return redirect()->route('offers');
                
                //dd($req);
                
         }
    
    
}
