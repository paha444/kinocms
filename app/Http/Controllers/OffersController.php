<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Model;

use App\Offers;

use App\Files;

use App\Message;

use App\Pages;


use Illuminate\Support\Facades\Auth;

use DB;
use Session;

class OffersController extends Controller
{
    //

         public function request($id){


            $Offer = DB::table('offers')->where('id', $id)->first();

            $offer_files = DB::table('offers_files')
            ->where('offers_files.offer_id', $Offer->id)
            ->join('files', 'files.id', '=', 'offers_files.file_id')
            ->select('files.filename')
            ->get();
            
            //$Pages = Pages::all();


            $lawyers = DB::table('offers_lawyers')
                  ->select('users.*')
                //->join('region', 'region.id_region', '=', 'city.id_region')
                //->where('region.name', '=', $request->region)
                  //->where($where)
                    
                  ->where('offers_lawyers.offer_id','=',$id)
                  //->where('status','=',1)
                  ->join('users', 'users.id', '=', 'offers_lawyers.to_user_id')
/*
                ->orWhere('region','LIKE',"%{$request->string}%")
                ->orWhere('city','LIKE',"%{$request->string}%")
                ->orWhere('district','LIKE',"%{$request->string}%")
                ->orWhere('street','LIKE',"%{$request->string}%")

  */              
                ->get();




            
            //dd($Offer);
            //dd($offers_images);

            return view('request', ['offer' => $Offer,'offer_files'=>$offer_files,'lawyers'=>$lawyers]);

        }
        
        

         public function request_check(Request $request){
         
         $user = Auth::user();   
            
         $req_all = $request->all();   
         
         $result = 'error';
         $data = '';
         $remove = array();
         $required = array();
         
         $lawyers = '';   
         $message = '';
         $submit = ''; 
         $city = '';   
         if(isset($req_all['city'])){
            $city = $req_all['city'];
         }   

        
////////// очищаем очередь и ставим на удаление ненужные элементы в форме      
        switch ($req_all['LastRequest']) {
            case 'RequestClient': 
                unset($req_all['RequestType']);
                unset($req_all['RequestIspaid']);
                unset($req_all['RequestRecipients']);
                unset($req_all['RequestRecipients_reg']);
                $remove = ['RequestType','RequestIspaid','RequestRecipients','RequestRecipients_reg'];
            break;
            case 'RequestType': 
                unset($req_all['RequestIspaid']);
                unset($req_all['RequestRecipients']);
                unset($req_all['RequestRecipients_reg']);
                $remove = ['RequestIspaid','RequestRecipients','RequestRecipients_reg'];
            break;
            case 'RequestIspaid': 
                unset($req_all['RequestRecipients']);
                unset($req_all['RequestRecipients_reg']);
                $remove = ['RequestRecipients','RequestRecipients_reg'];
            break;
            case 'RequestRecipients': 
                unset($req_all['RequestRecipients_reg']);
                $remove = ['RequestRecipients_reg'];
            break;
        }
/////////////

            
         if(isset($req_all['RequestClient'])){  
                
                
                switch ($req_all['RequestClient']) {
                    case 'privateperson':               //// Гражданин
                            
                            $id = 'RequestType';
                            $result = 'success';
                            $active = '';

                            //$remove = ['RequestType','RequestIspaid','RequestRecipients','RequestRecipients_reg'];

                            $data = view('request_help.RequestType', ['id' => $id,'active'=>$active])->render(); 

                            if(isset($req_all['RequestType'])){
                                switch ($req_all['RequestType']) {
                                    case 'question':                  //// Вопрос
                                    case 'task':                      //// Задание  
                                  
                                       $id = 'RequestIspaid';     
                                       $result = 'success';
                                       //$active = '';
                                       //$remove = ['RequestIspaid','RequestRecipients','RequestRecipients_reg'];
                                       
                                       $active = '';
                                       $data = view('request_help.RequestIspaid', ['id' => $id, 'active'=>$active])->render(); 
                                       
                                            if(isset($req_all['RequestIspaid'])){
                                                switch ($req_all['RequestIspaid']) {
                                
                                                    case 'free':                   //// Бесплатно
                                                       $id = 'RequestRecipients';     
                                                       $result = 'success';
                                                       $active = '';
                                                       //$remove = ['RequestRecipients','RequestRecipients_reg'];
                                                       
                                                       if(isset($req_all['RequestLawyer'])){
                                                            
                                                            $data = '';
                                                            
                                                            //$lawyers = $this->lawyers_ajax('lawyer',$city,);
                                                            //$lawyer = $this->lawyers_ajax('lawyer',$req_all['RequestLawyer']);
                                                            //$data = view('request_help.RequestLawyer', ['lawyer' => $lawyer])->render(); 
                                                            $submit = view('request_help.submit')->render();
                                                        
                                                       }else{
                                                       
                                                       
                                                            if(isset($req_all['RequestRecipients'])) $active = $req_all['RequestRecipients'];
                                                            $data = view('request_help.RequestRecipients_only_all', ['id' => $id, 'active'=>$active])->render(); 
     
                                                                if(isset($req_all['RequestRecipients'])){
                                                                    switch ($req_all['RequestRecipients']) {
                                                    
                                                                        case 'all':                              ///// Все юристы
                                                                           $id = 'RequestRecipients_reg';     
                                                                           $result = 'success';
                                                                           $active = '';
                                                                           $message = '';
                                                                           $data = '';
                                                                           //$lawyers = $this->lawyers_ajax('all');  
                                                                           $submit = view('request_help.submit')->render();
                                                                           
                                                                        break;
    
                                                                    }  
                                                                }   
                                                            
                                                        }                                                     
                                 
                                                    break;
                                                    case 'paid':                //////// Платно
                                                        $id = 'RequestRecipients';
                                                        $result = 'success';
                                                        //$remove = ['RequestRecipients','RequestRecipients_reg'];
                                                        
                                                        
                                                       if(isset($req_all['RequestLawyer'])){
                                                            
                                                            $data = '';
                                                            $submit = view('request_help.submit')->render();
                                                        
                                                       }else{
                                                        
                                                            if(Auth::check()){
                                                                    
                                                                            
                                                                $id = 'RequestRecipients';     
                                                                $result = 'success';
                                                                $active = '';                                
                                                                if(isset($req_all['RequestRecipients'])) $active = $req_all['RequestRecipients'];
                                                                $data = view('request_help.RequestRecipients', ['id' => $id, 'active'=>$active])->render(); 
    
                                                                        if(isset($req_all['RequestRecipients'])){
                                                                            switch ($req_all['RequestRecipients']) {
                                                            
                                                                                case 'all':                              ///// Все юристы
                                                                                   $id = 'RequestRecipients_reg';     
                                                                                   $result = 'success';
                                                                                   $active = '';
                                                                                   //$remove = ['RequestRecipients_reg'];
                                                                                   $data = '';
                                                                                   
                                                                                   if($user->amount<2000){
                                                                                        $message = 'Стоимость этой заявки от 2000KZ';
                                                                                   }else{
                                                                                        //$lawyers = $this->lawyers_ajax('all'); //view('request_help.RequestRecipients', ['id' => $id, 'active'=>$active])->render(); 
                                                                                        $message = 'Стоимость этой заявки от 2000KZ';
                                                                                        $submit = view('request_help.submit')->render();
                                                                                   } 
    
                                                                                break;
                                                                                case 'selected':                         // Только выбранные
                                                                                    $id = 'RequestRecipients_reg';
                                                                                    $result = 'success';
                                                                                    //$remove = ['RequestRecipients_reg'];
                                                                                    $data = '';
                                                                                    
                                                                                    if($user->amount<5000){
                                                                                        $message = 'Стоимость этой заявки от 5000KZ';
                                                                                    }else{
                                                                                        $message = 'Стоимость этой заявки от 5000KZ';
                                                                                        $lawyers = $this->lawyers_ajax('selected',$city); //view('request_help.RequestRecipients', ['id' => $id, 'active'=>$active])->render(); 
                                                                                        $submit = view('request_help.submit')->render();
                                                                                    } 
    
                                                                                break;
                                                                            }  
                                                                        }
                                                            
                                                             
                                                                    
                                                             }else{
                                    
                                                                $id = 'RequestRecipients';     
                                                                $result = 'success';
                                                                $active = '';                                
                                                                if(isset($req_all['RequestRecipients'])) $active = $req_all['RequestRecipients'];
                                                                $data = view('request_help.auth_error', ['id' => $id, 'active'=>$active])->render(); 
                                                                //$data = '2';
                                                              }
                                                          
                                                          }
                                                                
                                                    break;
                                                } 
                                                
                                                
                                                
                                                 
                                            }
                
                 
                                 break;   
                                 case 'court':    //// Представительство в суде 
                                    
                                    
                                        $id = 'RequestRecipients';     
                                        $result = 'success';
                                        $active = '';
                                        //$remove = ['RequestRecipients','RequestRecipients_reg'];                                
                                        
                                        
                                       if(isset($req_all['RequestLawyer'])){
                                            
                                            $data = '';
                                            $submit = view('request_help.submit')->render();
                                        
                                       }else{
                                        
                                        
                                        if(isset($req_all['RequestRecipients'])) $active = $req_all['RequestRecipients'];
                                        $data = view('request_help.RequestRecipients', ['id' => $id, 'active'=>$active])->render(); 

                                                if(isset($req_all['RequestRecipients'])){
                                                    switch ($req_all['RequestRecipients']) {
                                    
                                                        case 'all':                              ///// Все юристы
                                                           $id = 'RequestRecipients_reg';     
                                                           $result = 'success';
                                                           $active = '';
                                                           //$remove = ['RequestRecipients_reg'];
                                                           $data = '';
                                                           
                                                           //if($user->amount<2000){
                                                           //     $message = 'Стоимость этой услуги от 2000KZ';
                                                           //}else{
                                                                //$lawyers = $this->lawyers_ajax('all'); //view('request_help.RequestRecipients', ['id' => $id, 'active'=>$active])->render(); 
                                                                $submit = view('request_help.submit')->render();
                                                           //} 

                                                        break;
                                                        case 'selected':                         // Только выбранные
                                                            $id = 'RequestRecipients_reg';
                                                            $result = 'success';
                                                            //$remove = ['RequestRecipients_reg'];
                                                            $data = '';
                                                            
                                                            //if($user->amount<5000){
                                                            //    $message = 'Стоимость этой услуги от 5000KZ';
                                                            //}else{
                                                                $lawyers = $this->lawyers_ajax('selected',$city); //view('request_help.RequestRecipients', ['id' => $id, 'active'=>$active])->render(); 
                                                                $submit = view('request_help.submit')->render();
                                                            //} 

                                                        break;
                                                    }  
                                                }                                    
                                    
                                    }
                                    
                                    
                                    
                                 break;
                                   
                                } 
                                
                                
                                 
                            }            
                    
                    
                    
                    break;
                    case 'business':       ////////////// Бизнес
                    
                    
                    
                    
                    
                            $id = 'RequestType';
                            $result = 'success';
                            $active = '';

                            //$remove = ['RequestType','RequestIspaid','RequestRecipients','RequestRecipients_reg'];
                            
                            //echo $req_all['LastRequest'];
                                
                            $data = view('request_help.RequestType', ['id' => $id,'active'=>$active])->render(); 
                                        
                            if(isset($req_all['RequestType'])){
                            //if(isset($req_all['RequestType']) && $req_all['RequestType']!=$req_all['LastRequest']){
                                switch ($req_all['RequestType']) {
                                    case 'question':                  //// Вопрос
                                    case 'task':                      //// Задание  
                                       $id = 'RequestIspaid';     
                                       $result = 'success';
                                       //$active = '';
                                       //$remove = ['RequestIspaid','RequestRecipients','RequestRecipients_reg'];
                                       
                                       $active = '';
                                       $data = view('request_help.RequestIspaid', ['id' => $id, 'active'=>$active])->render(); 
                                       
                                            if(isset($req_all['RequestIspaid'])){
                                                switch ($req_all['RequestIspaid']) {
                                
                                                    case 'free':                   //// Бесплатно
                                                       $id = 'RequestRecipients';     
                                                       $result = 'success';
                                                       $active = '';
                                                       //$remove = ['RequestRecipients','RequestRecipients_reg'];
 
                                                       if(isset($req_all['RequestLawyer'])){
                                                            
                                                            $data = '';
                                                            $submit = view('request_help.submit')->render();
                                                        
                                                       }else{
 
                                                       
                                                            if(isset($req_all['RequestRecipients'])) $active = $req_all['RequestRecipients'];
                                                            $data = view('request_help.RequestRecipients_only_all', ['id' => $id, 'active'=>$active])->render(); 
 
                                                            if(isset($req_all['RequestRecipients'])){
                                                                switch ($req_all['RequestRecipients']) {
                                                
                                                                    case 'all':                              ///// Все юристы
                                                                       $id = 'RequestRecipients_reg';     
                                                                       $result = 'success';
                                                                       $active = '';
                                                                       $message = '';
                                                                       $data = '';
                                                                       //$lawyers = $this->lawyers_ajax('all');  
                                                                       $submit = view('request_help.submit')->render();
                                                                       
                                                                    break;

                                                                }  
                                                            }
                                                            
                                                        }                                                        
                                 
                                                    break;
                                                    case 'paid':                //////// Платно
                                                        $id = 'RequestRecipients';
                                                        $result = 'success';
                                                        //$remove = ['RequestRecipients','RequestRecipients_reg'];
                                                        
                                                        if(isset($req_all['RequestLawyer'])){
                                                            
                                                            $data = '';
                                                            $submit = view('request_help.submit')->render();
                                                        
                                                        }else{
                                                        
                                                            if(Auth::check()){
    
                                                                $id = 'RequestRecipients';     
                                                                $result = 'success';
                                                                $active = '';                                
    
                                                                 
                                                                if($user->amount<30000){ 
                                                                   $data = ''; 
                                                                   $message = 'У Вас на счету должно быть не менее 30000KZ'; 
                                                                    
                                                                }else{
                                                                            
                                                                        if(isset($req_all['RequestRecipients'])) $active = $req_all['RequestRecipients'];
                                                                        $data = view('request_help.RequestRecipients_business', ['id' => $id, 'active'=>$active])->render(); 
    
                                                                        if(isset($req_all['RequestRecipients'])){
                                                                            switch ($req_all['RequestRecipients']) {
                                                            
                                                                                case 'experienced':                              ///// Опытные
                                                                                   $id = 'RequestRecipients_reg';     
                                                                                   $result = 'success';
                                                                                   $active = '';
                                                                                   $data = '';
                                                                                   //$remove = ['RequestRecipients_reg'];
                                                                                   
                                                                                   //if($user->amount<2000){
                                                                                   //     $message = 'Стоимость этой услуги от 2000KZ';
                                                                                   //}else{
                                                                                        $lawyers = $this->lawyers_ajax('experienced',$city); //view('request_help.RequestRecipients', ['id' => $id, 'active'=>$active])->render(); 
                                                                                        $submit = view('request_help.submit')->render();
                                                                                   //} 
    
                                                                                break;
                                                                                case 'inexpensive':                         //////// Не дорогие
                                                                                    $id = 'RequestRecipients_reg';
                                                                                    $result = 'success';
                                                                                    $data = '';
                                                                                    //$remove = ['RequestRecipients_reg'];
                                                                                    
                                                                                    //if($user->amount<5000){
                                                                                        //$message = 'Стоимость этой услуги от 5000KZ';
                                                                                    //}else{
                                                                                        $lawyers = $this->lawyers_ajax('inexpensive',$city); //view('request_help.RequestRecipients', ['id' => $id, 'active'=>$active])->render(); 
                                                                                        $submit = view('request_help.submit')->render();
                                                                                    //} 
    
                                                                                break;
                                                                            }  
                                                                        }
                                                            
                                                                }
                                                                    
                                                             }else{
                                    
                                                                $id = 'RequestRecipients';     
                                                                $result = 'success';
                                                                $active = '';                                
                                                                if(isset($req_all['RequestRecipients'])) $active = $req_all['RequestRecipients'];
                                                                $data = view('request_help.auth_error', ['id' => $id, 'active'=>$active])->render(); 
                                                                //$data = '2';
                                                              }
                                                          
                                                          }
                                                                
                                                    break;
                                                } 
                                                
                                            }
                
                 
                                 break;   
                                 case 'court':    //// Представительство в суде 
                                    
                                    
                                        $id = 'RequestRecipients';     
                                        $result = 'success';
                                        $active = '';
                                        //$remove = ['RequestRecipients','RequestRecipients_reg'];                                
                                        

                                        if(isset($req_all['RequestLawyer'])){
                                            
                                            $data = '';
                                            $submit = view('request_help.submit')->render();
                                        
                                        }else{
                                        
                                        if(isset($req_all['RequestRecipients'])) $active = $req_all['RequestRecipients'];
                                        $data = view('request_help.RequestRecipients', ['id' => $id, 'active'=>$active])->render(); 

                                                if(isset($req_all['RequestRecipients'])){
                                                    switch ($req_all['RequestRecipients']) {
                                    
                                                        case 'all':                              ///// Все юристы
                                                           $id = 'RequestRecipients_reg';     
                                                           $result = 'success';
                                                           $active = '';
                                                           //$remove = ['RequestRecipients_reg'];
                                                           $data = '';
                                                           
                                                           //if($user->amount<2000){
                                                           //     $message = 'Стоимость этой услуги от 2000KZ';
                                                           //}else{
                                                                //$lawyers = $this->lawyers_ajax('all'); //view('request_help.RequestRecipients', ['id' => $id, 'active'=>$active])->render(); 
                                                                $submit = view('request_help.submit')->render();
                                                           //} 

                                                        break;
                                                        case 'selected':                         // Только выбранные
                                                            $id = 'RequestRecipients_reg';
                                                            $result = 'success';
                                                            //$remove = ['RequestRecipients_reg'];
                                                            $data = '';
                                                            
                                                            //if($user->amount<5000){
                                                            //    $message = 'Стоимость этой услуги от 5000KZ';
                                                            //}else{
                                                                $lawyers = $this->lawyers_ajax('selected',$city); //view('request_help.RequestRecipients', ['id' => $id, 'active'=>$active])->render(); 
                                                                $submit = view('request_help.submit')->render();
                                                            //} 

                                                        break;
                                                    }  
                                                }                                    
                                    
                                        }
                                    
                                    
                                    
                                 break;
                                } 
                                
                            }                    
                    

                    break;
                }         
            }

         if($req_all['LastRequest']=='submit') {
            
            $result = 'submit';
            
            if( empty($req_all['RequestRubric']) or empty($req_all['RequestCaption']) or empty($req_all['RequestDetails'])  ){
                
                $message = 'Заполните все обязательные поля';
            
                if(empty($req_all['RequestRubric'])) $required[] = 'RequestRubric';
                if(empty($req_all['RequestCaption'])) $required[] = 'RequestCaption';
                if(empty($req_all['RequestDetails'])) $required[] = 'RequestDetails';
            
            
            
            }
            
            //$result = ''
         }


         return response()->json(['result'=>$result,'data'=>$data,'id'=>$id,'remove'=>$remove,'lawyers'=>$lawyers,'message'=>$message,'submit'=>$submit,'required'=>$required]);

         
         }
    

    
  
         public function submit(Request $req)
         {
            $user = Auth::user();
            
            $req_all = $req->all();
            
            $RequestRubric = $req->input('RequestRubric');
            $RequestCaption = $req->input('RequestCaption');
            $RequestDetails = $req->input('RequestDetails');
            $RequestClient = $req->input('RequestClient');
            $RequestType = $req->input('RequestType');
            $RequestIspaid = $req->input('RequestIspaid');
            $RequestRecipients = $req->input('RequestRecipients');
            
            if(empty($RequestRubric) 
            or empty($RequestCaption)    
            or empty($RequestDetails)    
            or empty($RequestClient)    
            or empty($RequestType)    
            //or empty($RequestIspaid)    
            or empty($RequestRecipients)){

                    $result = 'error';
                    $message = 'Заполните все поля';
                
                
            }else{





                 if(isset($req_all['RequestClient'])){  
                        
                        
                        switch ($req_all['RequestClient']) {
                            case 'privateperson':               //// Гражданин
                                    
                                    if(isset($req_all['RequestType'])){
                                        switch ($req_all['RequestType']) {
                                            case 'question':                  //// Вопрос
                                            case 'task':                      //// Задание  
                                          
                                                    if(isset($req_all['RequestIspaid'])){
                                                        switch ($req_all['RequestIspaid']) {
                                        
                                                            case 'free':                   //// Бесплатно
        
                                                                    if(isset($req_all['RequestRecipients'])){
                                                                        switch ($req_all['RequestRecipients']) {
                                                        
                                                                            case 'all':                              ///// Все юристы
                                                                               
                                                                               
                                                                               
                                                                            break;
        
                                                                        }  
                                                                    }                                                        
                                         
                                                            break;
                                                            case 'paid':                //////// Платно
                                                                    
                                                                        if(isset($req_all['RequestRecipients'])){
                                                                            switch ($req_all['RequestRecipients']) {
                                                                                
                                                                                case 'all':
                                                                                
                                                                                        $price = 2000;                              ///// Все юристы
            
                                                                                break;
                                                                                case 'selected':                         // Только выбранные
            
                                                                                        $price = 5000;
                                                                                        
                                                                                break;
                                                                            }  
                                                                        }
        
                                                            break;
                                                        } 
                                                    }
                         
                                         break;   
                                         case 'court':    //// Представительство в суде 
                                            
        
                                                        if(isset($req_all['RequestRecipients'])){
                                                            switch ($req_all['RequestRecipients']) {
                                            
                                                                case 'all':                              ///// Все юристы
        
                                                                break;
                                                                
                                                                case 'selected':                         // Только выбранные
        
                                                                break;
                                                            }  
                                                        }                                    
                                            
                                         break;
                                           
                                        } 
                                    }            
                            
                            break;
                            case 'business':       ////////////// Бизнес
                            
                                                
                                    if(isset($req_all['RequestType'])){
                                        switch ($req_all['RequestType']) {
                                            case 'question':                  //// Вопрос
                                            case 'task':                      //// Задание  
                                               
                                                    if(isset($req_all['RequestIspaid'])){
                                                        switch ($req_all['RequestIspaid']) {
                                        
                                                            case 'free':                   //// Бесплатно
                                                               
                                                                    if(isset($req_all['RequestRecipients'])){
                                                                        switch ($req_all['RequestRecipients']) {
                                                        
                                                                            case 'all':                              ///// Все юристы
                                                                               
                                                                            break;
        
                                                                        }  
                                                                    }                                                        
                                         
                                                            break;
                                                            case 'paid':                //////// Платно
        
                                                                                
                                                                    if(isset($req_all['RequestRecipients'])){
                                                                        switch ($req_all['RequestRecipients']) {
                                                        
                                                                            case 'experienced':                              ///// Опытные
                                                                                    $price = 7000;     
                                                                            break;
                                                                            case 'inexpensive':                         //////// Не дорогие
                                                                                    $price = 5000; 
                                                                            break;
                                                                        }  
                                                                    }
        
                                                                        
                                                            break;
                                                        } 
                                                        
                                                    }
                        
                         
                                         break;   
                                         case 'court':    //// Представительство в суде 
                                            
                                                        if(isset($req_all['RequestRecipients'])){
                                                            switch ($req_all['RequestRecipients']) {
                                            
                                                                case 'all':                              ///// Все юристы
        
                                                                break;
                                                                case 'selected':                         // Только выбранные
        
                                                                break;
                                                            }  
                                                        }                                    
                                            
                                         break;
                                        } 
                                        
                                    }                    
        
                            break;
                        }         
                    }


                
                $Offer = new Offers();
                
                $Offer->Rubric = $req->input('RequestRubric');
                $Offer->RequestCaption = $req->input('RequestCaption');
                $Offer->RequestDetails = $req->input('RequestDetails');
                $Offer->RequestClient = $req->input('RequestClient');
                $Offer->RequestType = $req->input('RequestType');
                $Offer->RequestIspaid = $req->input('RequestIspaid');
                $Offer->RequestRecipients = $req->input('RequestRecipients');
                $Offer->status = 'new';
                
                if(isset($price)) $Offer->price = $price;

                
                
                if(Auth::user()){
                    $Offer->user_id = $user->id;
                }else{
                    $Offer->user_id = 2; //гость
                }
                $Offer->save();
                
               
                if(!Auth::user()){
                    
                    if (Session::has('offers')){
                        Session::push('offers', $Offer->id);
                    }else{
                        Session::put('offers',[$Offer->id]);
                    }
                }
                
                $files = $req->input('files');
                $lawyers = $req->input('lawyers');
                
                if(isset($files)){
                $sql = array();
                    foreach($files as $file){
                        $sql[] = array(
                          'offer_id' => $Offer->id,
                          'file_id' => $file,
                        );
                    }
                    DB::table('offers_files')->insert($sql);
                }         
                
                
                if(isset($lawyers)){
                $sql = array();
                    foreach($lawyers as $lawyer){
                        $sql[] = array(
                          'offer_id' => $Offer->id,
                          'to_user_id' => $lawyer,
                          'cost' => $price,
                          'status' => 'new',
                        );
                    }
                    DB::table('offers_lawyers')->insert($sql);
                }   
              
                
                

                if($Offer->id){
                    
                    //////////////// отправка в телеграм                    
                    $message_tg  = '<b>Добавлена новая заявка</b> - '.$Offer->RequestCaption.' <br><br>'; 
                    $message_tg .= '<b>Описание</b> - '.$Offer->RequestDetails; 
                    
                    //$message_tg  = "*Добавлена новая заявка* - ".$Offer->RequestCaption." \n" ;
                    //$message_tg .= "*Описание* - ".$Offer->RequestDetails." \n" ;
                    
                    $this->message_to_telegram($message_tg);
                    ////////////////
                    
                    
                    $result = 'success';
                    $message = view('request_help.request_success')->render();
                    $message .= view('request_help.request_auth')->render();
                }else{
                    $result = 'error';
                    $message = view('request_help.request_error')->render();
                }
            
            }
            
            return response()->json(['result'=>$result,'message'=>$message]);

         }


        
        public function message_to_telegram($text){
            
            $ch = curl_init();
            curl_setopt_array(
                $ch,
                array(
                    CURLOPT_URL => 'https://thesystem.pro/',
                    CURLOPT_POST => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_POSTFIELDS => array(
                        'action' => 'f24_notify',
                        'key' => '3k5ljsd3i',
                        'text' => $text,
                        
                    ),
                )
            );
            curl_exec($ch);
        }  


         public function lawyers_ajax($Recipients='',$city=''){

            
            $user = Auth::user();
            
            
            switch ($Recipients) {
                    case 'selected':  /// Только выбранные

                        //if($user->amount>0){
                        
                            $lawyers = DB::table('users')

                                        ->where(function($query) use($city) {
                                            if($city){
                                                $query->where('city','LIKE',$city.'%');
                                            }
                                        })
                                       
                                       ->where('role_id','=',5)
                                       ->where('status','=',1)
                                       ->get();
                        
                            $data = view('lawyers_ajax_selected', ['lawyers' => $lawyers,'city'=>$city])->render();  
                        
                        //}else{
                        //    $data = 'У вас счету должно быть больше 0 KZT';  
                        //}
                    
                    break;
                    
                    case 'experienced': /// Опытные

                        //if($user->amount>0){
                        
                            $lawyers = DB::table('users')
                                        ->where(function($query) use($city) {
                                            if($city){
                                                $query->where('city','LIKE',$city.'%');
                                            }
                                        })
                                       ->where('role_id','=',5)
                                       ->where('status','=',1)
                                       ->get();
                        
                            $data = view('lawyers_ajax_selected', ['lawyers' => $lawyers,'city'=>$city])->render();  
                        
                        //}else{
                        //    $data = 'У вас счету должно быть больше 0 KZT';  
                        //}
                    
                    break;
                    
                    case 'inexpensive': /// Недорогие

                        //if($user->amount>0){
                        
                            $lawyers = DB::table('users')
                                        ->where(function($query) use($city) {
                                            if($city){
                                                $query->where('city','LIKE',$city.'%');
                                            }
                                        })
                                       ->where('role_id','=',5)
                                       ->where('status','=',1)
                                       ->get();
                        
                            $data = view('lawyers_ajax_selected', ['lawyers' => $lawyers,'city'=>$city])->render();  
                        
                        //}else{
                        //    $data = 'У вас счету должно быть больше 0 KZT';  
                        //}
                    
                    break;
                    
                   
                    
                    case 'all': // Все
                    default:
                            
                            $lawyers = DB::table('users')
                                        ->where(function($query) use($city) {
                                            if($city){
                                                $query->where('city','LIKE',$city.'%');
                                            }
                                        })
                                       ->where('role_id','=',5)
                                       ->where('status','=',1)
                                       ->get();
                        
                            $data = view('lawyers_ajax_all', ['lawyers' => $lawyers,'city'=>$city])->render();  
 
                    break;
                    
                
                
            }
            

                
            
            
            
            return $data;
            //return response()->json(['data'=>$data]);

         }    


        public function business(Request $request)
        {
                


            $req = $request->all();
            
            $lawyers=[];
            

            $sort_by = 'users.id';
            $sort_order = 'DESC';

            switch ($request->sort_by) {
                case 'Rating':
                    $sort_by = 'users.rating';
                    break;
                case 'Experience':
                    $sort_by = 'users.work_experience';
                    break;
                case 'Age':
                    $sort_by = 'users.date';
                    break;
            } 

            switch ($request->sort_order) {
                case 'Desc':
                    $sort_order = 'DESC';
                    break;
                case 'Asc':
                    $sort_order = 'ASC';
                    break;
            } 


            
            if($request->rubr or $request->string){
                    
                    if($request->rubr and $request->string){
                    
                        $category_lawyer = DB::table('categories_lawyer')
                                   ->where('categories_lawyer.link','=',$request->rubr) 
                                   
                                   ->orWhere(function($query) use($request) {
                                          $query->where('categories_lawyer.name_ru','LIKE','%'.$request->string.'%') 
                                                ->orWhere('categories_lawyer.name_en','LIKE','%'.$request->string.'%') 
                                                ->orWhere('categories_lawyer.name_kz','LIKE','%'.$request->string.'%');
                                    })
                                   
                                   
                                   ->select('categories_lawyer.id')
                                   
                                   
                                   ->get();

/*
function($request){ 
                                            
if($request->sort_by=='Rating') {
    
     return true;

    switch ($request->sort_by) {
        case 'Rating':
            return 'rating';
            break;
        case 'Experience':
            return 'work_experience';
            break;
        case 'Age':
            return 'date';
            break;
    }                                                
    
}else{
    return false;
}

}*/
                    
                    
                    }elseif($request->rubr){
    
                        $category_lawyer = DB::table('categories_lawyer')
                                   ->where('categories_lawyer.link','=',$request->rubr) 
                                   ->get(); 
    
                    }elseif($request->string){
                        
                        $category_lawyer = DB::table('categories_lawyer')
                                   ->where('categories_lawyer.name_ru','LIKE','%'.$request->string.'%') 
                                   ->orWhere('categories_lawyer.name_en','LIKE','%'.$request->string.'%') 
                                   ->orWhere('categories_lawyer.name_kz','LIKE','%'.$request->string.'%') 
                                   ->select('categories_lawyer.id')
                                   ->get();
    
                    }
                    
    
                    $ids = [];
                                foreach($category_lawyer as $category){
                                    $ids[]=$category->id;
                                }
    //echo 1;
                    $lawyers = DB::table('users_lawyer_categories')
                               ->whereIn('users_lawyer_categories.categories_lawyer_id',$ids) 
                               ->Join('users','users.id','=','users_lawyer_categories.user_id') 
                               ->groupBy('users_lawyer_categories.user_id')
                               ->select('users.*')
                               
                               ->orderBy($sort_by, $sort_order)
                               
                               ->paginate(10);                 
            
            }else{
                
                //echo 2;
                
                //echo $sort_by,$sort_order;
                
                    $lawyers = DB::table('users')
                               ->where('role_id','=',5)
                               ->where('status','=',1)
                               ->orderBy($sort_by, $sort_order)
                               //->get();
                               ->paginate(10); 
                
            }
            
            
            
            
            
            
            
            $categories_lawyer = DB::table('categories_lawyer')->get();
            
            $Page = Pages::find(15);
        
            return view('business', ['lawyers' => $lawyers,'categories_lawyer'=>$categories_lawyer,'request'=>$req,'Page'=>$Page])->render();  
 
                

        }
        
        
        
        
        

        public function lawyers(Request $request)
        {
            
            $req = $request->all();
            
            $lawyers=[];
            

            $sort_by = 'users.id';
            $sort_order = 'DESC';

            switch ($request->sort_by) {
                case 'Rating':
                    $sort_by = 'users.rating';
                    break;
                case 'Experience':
                    $sort_by = 'users.work_experience';
                    break;
                case 'Age':
                    $sort_by = 'users.date';
                    break;
            } 

            switch ($request->sort_order) {
                case 'Desc':
                    $sort_order = 'DESC';
                    break;
                case 'Asc':
                    $sort_order = 'ASC';
                    break;
            } 


            
            if($request->rubr or $request->string){
                    
                    if($request->rubr and $request->string){
                    
                        $category_lawyer = DB::table('categories_lawyer')
                                   ->where('categories_lawyer.link','=',$request->rubr) 
                                   
                                   ->orWhere(function($query) use($request) {
                                          $query->where('categories_lawyer.name_ru','LIKE','%'.$request->string.'%') 
                                                ->orWhere('categories_lawyer.name_en','LIKE','%'.$request->string.'%') 
                                                ->orWhere('categories_lawyer.name_kz','LIKE','%'.$request->string.'%');
                                    })
                                   
                                   
                                   ->select('categories_lawyer.id')
                                   
                                   
                                   ->get();

/*
function($request){ 
                                            
if($request->sort_by=='Rating') {
    
     return true;

    switch ($request->sort_by) {
        case 'Rating':
            return 'rating';
            break;
        case 'Experience':
            return 'work_experience';
            break;
        case 'Age':
            return 'date';
            break;
    }                                                
    
}else{
    return false;
}

}*/
                    
                    
                    }elseif($request->rubr){
    
                        $category_lawyer = DB::table('categories_lawyer')
                                   ->where('categories_lawyer.link','=',$request->rubr) 
                                   ->get(); 
    
                    }elseif($request->string){
                        
                        $category_lawyer = DB::table('categories_lawyer')
                                   ->where('categories_lawyer.name_ru','LIKE','%'.$request->string.'%') 
                                   ->orWhere('categories_lawyer.name_en','LIKE','%'.$request->string.'%') 
                                   ->orWhere('categories_lawyer.name_kz','LIKE','%'.$request->string.'%') 
                                   ->select('categories_lawyer.id')
                                   ->get();
    
                    }
                    
    
                    $ids = [];
                                foreach($category_lawyer as $category){
                                    $ids[]=$category->id;
                                }
    //echo 1;
                    $lawyers = DB::table('users_lawyer_categories')
                               ->whereIn('users_lawyer_categories.categories_lawyer_id',$ids) 
                               ->Join('users','users.id','=','users_lawyer_categories.user_id') 
                               ->groupBy('users_lawyer_categories.user_id')
                               ->select('users.*')
                               
                               ->orderBy($sort_by, $sort_order)
                               
                               ->paginate(10);                 
            
            }else{
                
                //echo 2;
                
                //echo $sort_by,$sort_order;
                
                    $lawyers = DB::table('users')
                               ->where('role_id','=',5)
                               ->where('status','=',1)
                               ->orderBy($sort_by, $sort_order)
                               //->get();
                               ->paginate(10); 
                
            }
            
            
            
            
            
            
            
            $categories_lawyer = DB::table('categories_lawyer')->get();
            
        
            return view('lawyers', ['lawyers' => $lawyers,'categories_lawyer'=>$categories_lawyer,'request'=>$req])->render();  
            
        }


        public function lawyer($id)
        {
            
            //$user = User::find($id);
            
            $lawyer = DB::table('users')
                       ->where('id','=',$id)
                       ->where('role_id','=',5)
                       ->where('status','=',1)
                       ->first();
            
            
            if($lawyer){
                
                
                
                $reviews = DB::table('reviews')
                
                ->leftJoin('users', 'users.id', '=', 'reviews.from_user_id')
                
                ->where('to_user_id', $lawyer->id)
                
                ->select('users.*',
                'reviews.offer_id as rev_offer_id',
                'reviews.message as rev_message',
                'reviews.created_at as rev_created_at')
                ->paginate(10);
    
                
                 $blogs = DB::table('blogs')->where('user_id', $lawyer->id)->paginate(10);
    
                   foreach($blogs as $key=>$post){
        
        
                        $blog_images = DB::table('blogs_images')
                        ->where('blogs_images.blog_id', $post->id)
                        ->join('images', 'images.id', '=', 'blogs_images.image_id')
                        ->select('images.filename')
                        ->get()->toArray();
        
                        $blogs[$key]->images = $blog_images;
        
                   }                
                
                
                
                
                
                
                return view('lawyer', ['lawyer' => $lawyer,'reviews'=>$reviews,'blogs'=>$blogs]);//->render();  
            }else{
                return redirect()->route('lawyers')->with('message', 'Такой юрист ненайден');
 
            }
            
            
            //return view('lawyer');
        }






         
        public function success(Request $request){
            
            //$reg = $request->all();
            //print_r($reg);
            //die;
            
        } 

  
        public function add_file(Request $request)
        {
            //
    
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $file->move(public_path('files/'),$fileName);
             
/*            $imageUpload = new Image();
            $imageUpload->filename = $avatarName;
            $imageUpload->save();*/
            
            $id = DB::table('files')->insertGetId(
                ['filename' => $fileName]
            );            
            
            
            return response()->json(['success'=>$fileName,'id'=>$id]);
            
        }

        public function delete_file(Request $request)
        {
            
            
            $File = DB::table('files')->where('filename', $request->filetodelete)->first();
            
            DB::delete('delete from files where filename = ?',[$request->filetodelete]);
            
            $file_path = public_path('files').'/'.$request->filetodelete;
            
            //echo $image_path;
  
              if(File::exists($file_path)) {
                    File::delete($file_path);
              }
              
              return response()->json(['id'=>$File->id]);
        
        } 
    
    
    
    
    
    


         public function filter_address(Request $request)
         {
            
            $where = array();
            
            if(isset($request->type_offer)){
                
                if($request->type_offer=='sale') {
                        $where[]=['type_offer','=','sale'];
                }elseif($request->type_offer=='rent') {
                        $where[]=['type_offer','=','rent'];
                }elseif($request->type_offer=='new_building') {
                        $where[]=['new_building','=','1'];
                }
                
                
            }
            
            //print_r($where);
              
            
            
            $offers = DB::table('offers')
                //->select('city.name')
                //->join('region', 'region.id_region', '=', 'city.id_region')
                //->where('region.name', '=', $request->region)
                  ->where($where)
                
                  ->where('country','LIKE',"%{$request->string}%")
                ->orWhere('region','LIKE',"%{$request->string}%")
                ->orWhere('city','LIKE',"%{$request->string}%")
                ->orWhere('district','LIKE',"%{$request->string}%")
                ->orWhere('street','LIKE',"%{$request->string}%")

                ->get();

                
                $out = '';
                
                foreach($offers as $offer){
                    
                    $country = '';
                    $region = '';
                    $city = '';
                    $district = '';
                    $street = '';
                    
                    if(isset($offer->country)) $country = $offer->country.' ';
                    if(isset($offer->region)) $region = $offer->region.' ';
                    if(isset($offer->city)) $city = $offer->city.' ';
                    if(isset($offer->district)) $district = $offer->district.' ';
                    if(isset($offer->street)) $street = $offer->street.' ';
                    
                    $out .= '<p><a href="/offer/'.$offer->id.'">'.$country.$region.$city.$district.$street.'</a></p>';
                    
                }
                
                return response()->json(['data'=>$out]);
                
         }


         public function offer($id){


            $Offer = DB::table('offers')->where('id', $id)->first();

            $offer_images = DB::table('offers_images')
            ->where('offers_images.offer_id', $Offer->id)
            ->join('images', 'images.id', '=', 'offers_images.image_id')
            ->select('images.filename')
            ->get();
            
            $Pages = Pages::all();
            
            //dd($Offer);
            //dd($offers_images);

            return view('offer', ['offer' => $Offer,'offer_images'=>$offer_images,'pages'=>$Pages]);

        }


        public function send_message(Request $request,$id){
            
            
            //dd($request);
 
 
            $user = Auth::user();
  
  
  //dd($id);

            $file = $request->file('file');
            
            if($file){
            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('files'),$fileName); 

                
                $File = new Files();
                
                $File->filename = $fileName;
                
                $File->save();
                
                //$File_id = $File->id;
            
            //}else{
                
                //$File_id = null;
                
            }
            
            //dd($file);
            
    
            $Offer = Offers::find($id);

            $offer_images = DB::table('offers_images')
            ->where('offers_images.offer_id', $Offer->id)
            ->join('images', 'images.id', '=', 'offers_images.image_id')
            ->select('images.filename')
            ->get();
            



            
            
            //DB::table('files')->insert($sql);
            
            $Message = new Message();
            
            $Message->offer_id = $Offer->id;
            
            $Message->message = $request->areas;
            
            if($file){
            $Message->file_id = $File->id;
            }
            
            $Message->from_user_id = $user->id;

            $Message->to_user_id = $Offer->user_id;
            
            
            
            $Message->save();
            
            
            
            
            //DB::table('offers_images')->insert($sql); 
 
 
 
            //return view('offer', ['offer' => $Offer,'offer_images'=>$offer_images,'Message'=>$Message]);

            //return redirect()->route('offer', $Offer->id);
            
            
            
        }



         public function offers_ajax(Request $request){
            
            
            //dd($request->all());
            
            //$filter = $request;
            //dd($filter);
            //$query = $request->query;
            //dd($request->all());

            $where = array();
            $where2 = array();
            $where3 = array();
            $where4 = array();
            $where5 = array();
            $where6 = array();
            $where7 = array();
            $where8 = array();
            
            
            
            $filter['filter'] = array();
            
            $has_photo = false;

            if($request->all()){

            $all = $request->all();
            
            //$filter = $all['filter'];
            
            $filter['filter'] = $all['filter'];
            
            //dd($filter);
            
            
            if(isset($filter['filter']['type_offer'])):
                $where[]=['offers.type_offer','=',$filter['filter']['type_offer']];
            else:
                $filter['filter']['type_offer']='';
            endif;  
            
            
            if(isset($filter['filter']['new_building'])):
                $where[]=['offers.new_building','=',$filter['filter']['new_building']];
            else:
                $filter['filter']['new_building']='';
            endif;  
            




            if(isset($filter['filter']['building_material'])):
                $where[]=['offers.building_material','=',$filter['filter']['building_material']];
            else:
                $filter['filter']['building_material']='';
            endif;  
 
            if(isset($filter['filter']['renovation'])):
                $where[]=['offers.renovation','=','1'];
            else:
                $filter['filter']['renovation']='';
            endif;

            if(isset($filter['filter']['type_sale'])):
                $where[]=['offers.type_sale','=',$filter['filter']['type_sale']];
            else:
                $filter['filter']['type_sale']='';
            endif;  

            if(isset($filter['filter']['distance_metro_p'])):
                $where[]=['offers.distance_metro_p','=',$filter['filter']['distance_metro_p']];
            else:
                $filter['filter']['distance_metro_p']='';
            endif;  

            if(isset($filter['filter']['distance_metro_t'])):
                $where[]=['offers.distance_metro_t','=',$filter['filter']['distance_metro_t']];
            else:
                $filter['filter']['distance_metro_t']='';
            endif;  

            if(isset($filter['filter']['area_kitchen'])):
                $where[]=['offers.area_kitchen','=',$filter['filter']['area_kitchen']];
            else:
                $filter['filter']['area_kitchen']='';
            endif;  

            if(isset($filter['filter']['isolated_rooms'])):
                $where[]=['offers.isolated_rooms','=','1'];
            else:
                $filter['filter']['isolated_rooms']='';
            endif;

            if(isset($filter['filter']['apartments'])):
                $where[]=['offers.apartments','=',$filter['filter']['apartments']];
            else:
                $filter['filter']['apartments']='';
            endif;  

            if(isset($filter['filter']['ceiling_height'])):
                $where[]=['offers.ceiling_height','=',$filter['filter']['ceiling_height']];
            else:
                $filter['filter']['ceiling_height']='';
            endif;  

            if(isset($filter['filter']['year_built'])):
                $where[]=['offers.year_built','=',$filter['filter']['year_built']];
            else:
                $filter['filter']['year_built']='';
            endif;  

            if(isset($filter['filter']['repair'])):
                $where[]=['offers.repair','=',$filter['filter']['repair']];
            else:
                $filter['filter']['repair']='';
            endif;  

            if(isset($filter['filter']['separate_bathroom'])):
                $where[]=['offers.separate_bathroom','=','1'];
            else:
                $filter['filter']['separate_bathroom']='';
            endif;

            if(isset($filter['filter']['balcony'])):
                $where[]=['offers.balcony','=',$filter['filter']['balcony']];
            else:
                $filter['filter']['balcony']='';
            endif;  

            if(isset($filter['filter']['lift'])):
                $where[]=['offers.lift','=',$filter['filter']['lift']];
            else:
                $filter['filter']['lift']='';
            endif;  


            if(isset($filter['filter']['type_offer_period'])):
                $where[]=['offers.type_offer_period','=',$filter['filter']['type_offer_period']];
            else:
                $filter['filter']['type_offer_period']='';
            endif;  



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



if(isset($filter['filter']['price_from']) && isset($filter['filter']['price_to'])):

                $where2[]=['offers.price','>=',$filter['filter']['price_from']];
                $where2[]=['offers.price','<=',$filter['filter']['price_to']];
                $filter['filter']['price_from']='';
                $filter['filter']['price_to']='';


else:

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

            if(isset($filter['filter']['district'])):
                $where[]=['offers.district','=',$filter['filter']['district']];
            else:
                $filter['filter']['district']='';
            endif;
            

            if(isset($filter['filter']['address'])):
                $where4[]=['offers.country','LIKE',"%".$filter['filter']['address']."%"];
                $where5[]=['offers.region','LIKE',"%".$filter['filter']['address']."%"];
                $where6[]=['offers.city','LIKE',"%".$filter['filter']['address']."%"];
                $where7[]=['offers.district','LIKE',"%".$filter['filter']['address']."%"];
                $where8[]=['offers.street','LIKE',"%".$filter['filter']['address']."%"];
            else:
                $filter['filter']['address']='';
            endif;









            if(isset($filter['filter']['area_from']) && isset($filter['filter']['area_to'])):

                $where3[]=['offers.area','>=',$filter['filter']['area_from']];
                $where3[]=['offers.area','<=',$filter['filter']['area_to']];
                $filter['filter']['area_from']='';
                $filter['filter']['area_to']='';
            
            else:
                if(isset($filter['filter']['area_from'])):
                    $where[]=['offers.area','>=',$filter['filter']['area_from']];
                else:
                    $filter['filter']['area_from']='';
                endif;
    
                if(isset($filter['filter']['area_to'])):
                    $where[]=['offers.area','<=',$filter['filter']['area_to']];
                else:
                    $filter['filter']['area_to']='';
                endif;
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



            if(isset($filter['filter']['no_first_last'])):
                $where[]=['offers.floor','>','1'];
                //$where[]=['offers.floor','<','offers.number_floors'];
            else:
                $filter['filter']['no_first_last']='';
            endif;



            if(isset($filter['filter']['has_photo'])):
                //$join_images[]=['offers_images', 'offers_images.offer_id', '=', 'offers.id'];
                $has_photo = true;
            else:
                $filter['filter']['has_photo']='';
            endif;


            if(isset($filter['filter']['buy_mortgage'])):
                $where[]=['offers.buy_mortgage','=','1'];
            else:
                $filter['filter']['buy_mortgage']='';
            endif;



            }else{
                  
                  $filter['filter']['type_offer'] = ""; 
                  $filter['filter']['new_building'] = ""; 
                  
                  
                  
                  $filter['filter']['type_offer_period'] = ""; 
                   
                  $filter['filter']['type_object'] = "";
                  $filter['filter']['floor'] = "";
                  $filter['filter']['number_floors'] = "";
                  $filter['filter']['price_from'] = "";
                  $filter['filter']['price_to'] = "";
                  $filter['filter']['rooms'] = "";
                  $filter['filter']['country'] = "";
                  $filter['filter']['region'] = "";
                  $filter['filter']['district'] = "";
                  $filter['filter']['address']='';
                  
                  $filter['filter']['area_from'] = "";
                  $filter['filter']['area_to'] = "";
                  $filter['filter']['furniture'] = "";
                  $filter['filter']['parking'] = "";


                  $filter['filter']['no_first_last'] = "";
                  $filter['filter']['buy_mortgage'] = "";
                  $filter['filter']['has_photo']='';


                  $filter['filter']['building_material']='';
                  $filter['filter']['renovation']='';
                  $filter['filter']['type_sale']='';
                  $filter['filter']['distance_metro_p']='';
                  $filter['filter']['distance_metro_t']='';
                  $filter['filter']['area_kitchen']='';
                  $filter['filter']['isolated_rooms']='';
                  $filter['filter']['apartments']='';
                  $filter['filter']['ceiling_height']='';
                  $filter['filter']['year_built']='';
                  $filter['filter']['repair']='';
                  $filter['filter']['separate_bathroom']='';
                  $filter['filter']['balcony']='';
                  $filter['filter']['lift']='';





/*
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
*/



                
            }
            
            //dd($where);

/*            $offers = DB::table('offers')
            ->where($where)
            ->join('users', 'users.id', '=', 'offers.user_id')
            ->select('offers.*','users.name')
            ->get();*/



/*                  ->where('country','LIKE',"%{$request->string}%")
                ->orWhere('region','LIKE',"%{$request->string}%")
                ->orWhere('city','LIKE',"%{$request->string}%")
                ->orWhere('district','LIKE',"%{$request->string}%")
                ->orWhere('street','LIKE',"%{$request->string}%")

*/




            
            if($has_photo):
                //echo 1;
                $offers = DB::table('offers')
                ->where($where)
                ->where($where2)
                ->where($where3)
                //->where($where4)
                //->orWhere($where5)

                ->where(function($query) use ($where4,$where5,$where6,$where7,$where8)
                {
                    $query->where($where4)
                          ->orWhere($where5)
                          ->orWhere($where6)
                          ->orWhere($where7)
                          ->orWhere($where8);
                })                
                
                ->join('users', 'users.id', '=', 'offers.user_id')
                ->join('offers_images', 'offers_images.offer_id', '=', 'offers.id')
                ->select('offers.*','users.name')
                ->groupBy('offers_images.offer_id')
                ->get();
            
            else:
                //echo 2;
                
                // dd($where2);
                
                $offers = DB::table('offers')
                ->where($where)
                ->where($where2)
                ->where($where3)
                              
                ->where(function($query) use ($where4,$where5,$where6,$where7,$where8)
                {
                    $query->where($where4)
                          ->orWhere($where5)
                          ->orWhere($where6)
                          ->orWhere($where7)
                          ->orWhere($where8);
                })                
                
                
                ->join('users', 'users.id', '=', 'offers.user_id')
                ->select('offers.*','users.name')
                ->get();

            endif;            
            
            


           foreach($offers as $key=>$offer){


                $offer_images = DB::table('offers_images')
                ->where('offers_images.offer_id', $offer->id)
                ->join('images', 'images.id', '=', 'offers_images.image_id')
                ->select('images.filename')
                ->get();

                $offers[$key]->images = $offer_images;

           }
            
            
            //$map = ($filter['filter']['type_offer']=='sale' ? 'map' : 'map2');
            
            if($filter['filter']['type_offer']=='sale'){
                $map = 'map';
            }elseif($filter['filter']['type_offer']=='rent'){
                $map = 'map2';
            }elseif($filter['filter']['new_building']==1){
                $map = 'map3';
            }
            
            
            
            $data = view('offers_ajax', ['offers' => $offers,'filter'=>$filter['filter'],'map'=>$map])->render();  
            
            //dd($data);
            
            return response()->json(['data'=>$data,'offers'=>$offers]);
            

         }


         public function offers(Request $request){

        //dd($type_offer);
            
//$offers = App\Offers::all();
//return view('admin.offers', ['offers' => $offers]);



            $where = array();

            $where4 = array();
            $where5 = array();
            $where6 = array();
            $where7 = array();
            $where8 = array();


            $has_photo = false;
            
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

            if ($request->is('offers/new_building*')) {
             //  $where[]=['offers.type_offer','=','rent']; 
               $active_tab = 'new_building';
            }

            if ($request->is('offers/calc*')) {
             //  $where[]=['offers.type_offer','=','rent']; 
               $active_tab = 'calc';
            }




            if($request->query()){

            $filter = $request->query();


            //dd($filter['filter']);



            if(isset($filter['filter']['type_offer'])):
                $active_tab = $filter['filter']['type_offer'];
                $where[]=['offers.type_offer','=',$filter['filter']['type_offer']];
            else:
                $filter['filter']['type_offer']='sale';
            endif;       
            
            if(isset($filter['filter']['type_offer_period'])):
                $where[]=['offers.type_offer_period','=',$filter['filter']['type_offer_period']];
            else:
                $filter['filter']['type_offer_period']='';
            endif;       
            


            if(isset($filter['filter']['building_material'])):
                $where[]=['offers.building_material','=',$filter['filter']['building_material']];
            else:
                $filter['filter']['building_material']='';
            endif;  
 
            if(isset($filter['filter']['renovation'])):
                $where[]=['offers.renovation','=','1'];
            else:
                $filter['filter']['renovation']='';
            endif;

            if(isset($filter['filter']['type_sale'])):
                $where[]=['offers.type_sale','=',$filter['filter']['type_sale']];
            else:
                $filter['filter']['type_sale']='';
            endif;  

            if(isset($filter['filter']['distance_metro_p'])):
                $where[]=['offers.distance_metro_p','=',$filter['filter']['distance_metro_p']];
            else:
                $filter['filter']['distance_metro_p']='';
            endif;  

            if(isset($filter['filter']['distance_metro_t'])):
                $where[]=['offers.distance_metro_t','=',$filter['filter']['distance_metro_t']];
            else:
                $filter['filter']['distance_metro_t']='';
            endif;  

            if(isset($filter['filter']['area_kitchen'])):
                $where[]=['offers.area_kitchen','=',$filter['filter']['area_kitchen']];
            else:
                $filter['filter']['area_kitchen']='';
            endif;  

            if(isset($filter['filter']['isolated_rooms'])):
                $where[]=['offers.isolated_rooms','=','1'];
            else:
                $filter['filter']['isolated_rooms']='';
            endif;

            if(isset($filter['filter']['apartments'])):
                $where[]=['offers.apartments','=',$filter['filter']['apartments']];
            else:
                $filter['filter']['apartments']='';
            endif;  

            if(isset($filter['filter']['ceiling_height'])):
                $where[]=['offers.ceiling_height','=',$filter['filter']['ceiling_height']];
            else:
                $filter['filter']['ceiling_height']='';
            endif;  

            if(isset($filter['filter']['year_built'])):
                $where[]=['offers.year_built','=',$filter['filter']['year_built']];
            else:
                $filter['filter']['year_built']='';
            endif;  

            if(isset($filter['filter']['repair'])):
                $where[]=['offers.repair','=',$filter['filter']['repair']];
            else:
                $filter['filter']['repair']='';
            endif;  

            if(isset($filter['filter']['separate_bathroom'])):
                $where[]=['offers.separate_bathroom','=','1'];
            else:
                $filter['filter']['separate_bathroom']='';
            endif;

            if(isset($filter['filter']['balcony'])):
                $where[]=['offers.balcony','=',$filter['filter']['balcony']];
            else:
                $filter['filter']['balcony']='';
            endif;  

            if(isset($filter['filter']['lift'])):
                $where[]=['offers.lift','=',$filter['filter']['lift']];
            else:
                $filter['filter']['lift']='';
            endif;  


                                                                             

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

            if(isset($filter['filter']['district'])):
                $where[]=['offers.district','=',$filter['filter']['district']];
            else:
                $filter['filter']['district']='';
            endif;


            if(isset($filter['filter']['address'])):
                $where4[]=['offers.country','LIKE',"%".$filter['filter']['address']."%"];
                $where5[]=['offers.region','LIKE',"%".$filter['filter']['address']."%"];
                $where6[]=['offers.city','LIKE',"%".$filter['filter']['address']."%"];
                $where7[]=['offers.district','LIKE',"%".$filter['filter']['address']."%"];
                $where8[]=['offers.street','LIKE',"%".$filter['filter']['address']."%"];
            else:
                $filter['filter']['address']='';
            endif;



            if(isset($filter['filter']['area_from'])):
                $where[]=['offers.area','>=',$filter['filter']['area_from']];
            else:
                $filter['filter']['area_from']='';
            endif;

            if(isset($filter['filter']['area_to'])):
                $where[]=['offers.area','<=',$filter['filter']['area_to']];
            else:
                $filter['filter']['area_to']='';
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




            if(isset($filter['filter']['no_first_last'])):
                $where[]=['offers.floor','>','1'];
                $where[]=['offers.floor','<','offers.number_floors'];
            else:
                $filter['filter']['no_first_last']='';
            endif;



            if(isset($filter['filter']['has_photo'])):
                //$join_images[]=['offers_images', 'offers_images.offer_id', '=', 'offers.id'];
                $has_photo = true;
            else:
                $filter['filter']['has_photo']='';
            endif;


            if(isset($filter['filter']['buy_mortgage'])):
                $where[]=['offers.buy_mortgage','=','1'];
            else:
                $filter['filter']['buy_mortgage']='';
            endif;






            }else{
                
                  $filter['filter']['type_offer'] = "";
                  $filter['filter']['type_offer_period'] = ""; 
                  $filter['filter']['type_object'] = "";
                  $filter['filter']['floor'] = "";
                  $filter['filter']['number_floors'] = "";
                  $filter['filter']['price_from'] = "";
                  $filter['filter']['price_to'] = "";
                  $filter['filter']['rooms'] = "";
                  $filter['filter']['country'] = "";
                  $filter['filter']['region'] = "";
                  $filter['filter']['city'] = "";
                  $filter['filter']['district'] = "";
                  $filter['filter']['address'] = "";
                  
                  
                  
                  $filter['filter']['area_from'] = "";
                  $filter['filter']['area_to'] = "";
                  $filter['filter']['furniture'] = "";
                  $filter['filter']['parking'] = "";


                  $filter['filter']['no_first_last'] = "";
                  $filter['filter']['buy_mortgage'] = "";
                  $filter['filter']['has_photo']='';


                  $filter['filter']['building_material']='';
                  $filter['filter']['renovation']='';
                  $filter['filter']['type_sale']='';
                  $filter['filter']['distance_metro_p']='';
                  $filter['filter']['distance_metro_t']='';
                  $filter['filter']['area_kitchen']='';
                  $filter['filter']['isolated_rooms']='';
                  $filter['filter']['apartments']='';
                  $filter['filter']['ceiling_height']='';
                  $filter['filter']['year_built']='';
                  $filter['filter']['repair']='';
                  $filter['filter']['separate_bathroom']='';
                  $filter['filter']['balcony']='';
                  $filter['filter']['lift']='';

                
            }

/*            foreach($filter['filter'] as $key=>$val){

                if($val!=null){

                    $where[]=['offers.floor','=',$filter['filter']['floor']];

                }

            }*/


            if($has_photo):

                $offers = DB::table('offers')
                ->where($where)
                
               ->where(function($query) use ($where4,$where5,$where6,$where7,$where8)
                {
                    $query->where($where4)
                          ->orWhere($where5)
                          ->orWhere($where6)
                          ->orWhere($where7)
                          ->orWhere($where8);
                })                
                
                
                
                ->join('users', 'users.id', '=', 'offers.user_id')
                ->join('offers_images', 'offers_images.offer_id', '=', 'offers.id')
                ->select('offers.*','users.name')
                ->get();
            
            else:

                $offers = DB::table('offers')
                ->where($where)
                
               ->where(function($query) use ($where4,$where5,$where6,$where7,$where8)
                {
                    $query->where($where4)
                          ->orWhere($where5)
                          ->orWhere($where6)
                          ->orWhere($where7)
                          ->orWhere($where8);
                })                
                
                
                ->join('users', 'users.id', '=', 'offers.user_id')
                ->select('offers.*','users.name')
                ->get();

            endif;            
            


           foreach($offers as $key=>$offer){


                $offer_images = DB::table('offers_images')
                ->where('offers_images.offer_id', $offer->id)
                ->join('images', 'images.id', '=', 'offers_images.image_id')
                ->select('images.filename')
                ->get()->toArray();

                $offers[$key]->images = $offer_images;

                //$images = DB::table('offers_images')

                //->join('users', 'users.id', '=', 'offers.user_id')
               // ->leftjoin('offers_images', 'offers_images.offer_id', '=', 'offers.id')
               // ->leftjoin('images', 'images.id', '=', 'offers_images.image_id')
                //->select('offers.*','users.name','images.filename')
                //->select('offers.*','users.name')
                //->groupBy('offers_images.offer_id')
                //->orderBy('offers.id')

                //->groupBy('offers.id')

                //->get();

           }

           //dd($offers);
           
           
           
           
           
           
           $Pages = Pages::all();

/*$offers = DB::table('offers')
    ->leftJoin('offers_images', 'offers_images.offer_id', '=', 'offers.id')
    ->leftJoin('images', function ($leftJoin) {
        $leftJoin->on('images.id', '=', 'offers_images.image_id')
             //->where('recharge.create_date', '=', DB::raw("(select max(`create_date`) from recharge)"));

    })
    ->groupBy('offers_images.offer_id')
    ->get();*/

        //dd($offers);

            return view('offers', ['offers' => $offers,'filter'=>$filter['filter'],'active_tab'=>$active_tab,'pages'=>$Pages]);        

            // return view('admin.offers');
         }


}
