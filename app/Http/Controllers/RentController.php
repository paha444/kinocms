<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Rents;

use Illuminate\Support\Facades\Auth;

use File;

use DB;

class RentController extends Controller
{
    //
    
         public function rental_request(Request $request){




            $where = array();
            $filter['filter'] = array();
            
            $active_tab = '';

            if ($request->is('offers/sale*')) {
             //  $where[]=['offers.type_offer','=','sale']; 
               $active_tab = 'sale';
            }
            if ($request->is('offers/rent*')) {
             //  $where[]=['offers.type_offer','=','rent']; 
               $active_tab = 'rent';
            }


            if($request->query()){

            $filter = $request->query();


            //dd($filter['filter']);



/*            if(isset($filter['filter']['type_offer'])):
                $where[]=['offers.type_offer','=',$filter['filter']['type_offer']];
            else:
                $filter['filter']['type_offer']='';
            endif;  */ 
            
            $filter['filter']['type_offer'] = 'rent';
                                                                                 

            if(isset($filter['filter']['type_object'])):
                $where[]=['offers.type_object','=',$filter['filter']['type_object']];
            else:
                $filter['filter']['type_object']='';
            endif;                                                                        

            if(isset($filter['filter']['floor'])):
                $where[]=['offers.floor','=',$filter['filter']['floor']];
            else:
                $filter['filter']['floor']='';
            endif;                        

            if(isset($filter['filter']['number_floors'])):
                $where[]=['offers.number_floors','=',$filter['filter']['number_floors']];
            else:
                $filter['filter']['number_floors']='';
            endif;

            if(isset($filter['filter']['price_from'])):
                $where[]=['offers.price','>=',$filter['filter']['price_from']];
            else:
                $filter['filter']['price_from']='';
            endif;

            if(isset($filter['filter']['price_to'])):
                $where[]=['offers.price','<=',$filter['filter']['price_to']];
            else:
                $filter['filter']['price_to']='';
            endif;

            if(isset($filter['filter']['rooms'])):
                $where[]=['offers.rooms','=',$filter['filter']['rooms']];
            else:
                $filter['filter']['rooms']='';
            endif;

            if(isset($filter['filter']['country'])):
                $where[]=['offers.country','=',$filter['filter']['country']];
            else:
                $filter['filter']['country']='';
            endif;

            if(isset($filter['filter']['region'])):
                $where[]=['offers.region','=',$filter['filter']['region']];
            else:
                $filter['filter']['region']='';
            endif;

            if(isset($filter['filter']['city'])):
                $where[]=['offers.city','=',$filter['filter']['city']];
            else:
                $filter['filter']['city']='';
            endif;

            if(isset($filter['filter']['area'])):
                $where[]=['offers.area','=',$filter['filter']['area']];
            else:
                $filter['filter']['area']='';
            endif;

            if(isset($filter['filter']['furniture'])):
                $where[]=['offers.furniture','=','1'];
            else:
                $filter['filter']['furniture']='';
            endif;

            if(isset($filter['filter']['parking'])):
                $where[]=['offers.parking','=','1'];
            else:
                $filter['filter']['parking']='';
            endif;

            }else{
                
                  $filter['filter']['type_offer'] = "";
                  $filter['filter']['type_object'] = "";
                  $filter['filter']['floor'] = "";
                  $filter['filter']['number_floors'] = "";
                  $filter['filter']['price_from'] = "";
                  $filter['filter']['price_to'] = "";
                  $filter['filter']['rooms'] = "";
                  $filter['filter']['country'] = "";
                  $filter['filter']['region'] = "";
                  $filter['filter']['city'] = "";
                  $filter['filter']['area'] = "";
                  $filter['filter']['furniture'] = "";
                  $filter['filter']['parking'] = "";

                
            }
            
            
          //  $active_tab = 'rent';
          //  $filter['filter'] = array();

        
//$offers = App\Offers::all();
//return view('admin.offers', ['offers' => $offers]); 
            $user = Auth::user();

            $rents = DB::table('rents')
            //->where('user_id', $user->id)
            ->join('users', 'users.id', '=', 'rents.user_id')
            ->select('rents.*','users.name')
            ->get();
            
            //$offer_images = array();
            
            //return view('offer', ['offer' => $rents,'offer_images'=>$offer_images]);
            
            return view('offers', ['offers' => $rents,'filter'=>$filter['filter'],'active_tab'=>$active_tab]);
        
            //return view('rental_request', ['rents' => $rents]);        

            // return view('admin.offers');
         }
    
}
