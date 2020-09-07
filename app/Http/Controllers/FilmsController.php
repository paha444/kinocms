<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class FilmsController extends Controller
{



         public function afisha(){
        
            $films = DB::table('films')
            ->where('status',1)
            ->orderByDesc('films.id')->get();
            //->paginate(20);
            
            //print_r($films);
            
            return view('index', ['films' => $films]);        

         }
    
         public function film($id)
         {
            
            
            $Film = DB::table('films')->where('id', $id)->first();
            
            return view('film', ['Film' => $Film]);     


         }



}
