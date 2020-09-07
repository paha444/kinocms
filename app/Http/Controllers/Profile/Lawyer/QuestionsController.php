<?php

namespace App\Http\Controllers\Profile\Lawyer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Illuminate\Support\Facades\Auth;

class QuestionsController extends Controller
{


    public function questions_answer_submit(Request $request)
    {
    
        //$req = $request->all();
        if(Auth::guest()){
            $user_id = null;
        }else{
            $user_id = Auth::user()->id;
        }
    
   
        if(isset($request->message) && $request->message!=''){
        
            DB::table('questions_answers')->insert([
            'questions_id'=>$request->question_id,
            'comment'=>$request->message,
            'user_id'=>$user_id
            ]);
            
            return response()->json(['result'=>'success']);
    
        
        }else{
            
            return response()->json(['result'=>'error']);            
           
            
        }
    
    
    //return $return;
    

    }

}
