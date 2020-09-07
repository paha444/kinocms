<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Questions;

use DB;

use App\Http\Middleware\LocaleMiddleware;

use App\Files;

use Session;

use Illuminate\Support\Facades\Auth;

class QuestionsController extends Controller
{
    //


    public function categories_lawyer()
    {
                
        $lang = LocaleMiddleware::getLocale();
        
        //..echo $lang;
        
        $categories_lawyer = [];

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

        
        return $categories_lawyer; 

    }

    
    
    public function questions()
    {

        $questions = DB::table('questions')
        ->where(['is_enabled'=>1])
        ->orderBy('id')
        //->select('id','name_kz as name')
        ->get();


        foreach($questions as $key=>$question){
        
        
            $questions_answers = DB::table('questions_answers')
            ->where('questions_answers.questions_id', $question->id)
            ->where('questions_answers.is_enabled', 1)
            ->get()->toArray();
        
            $questions[$key]->answers = $questions_answers;
            
            $questions[$key]->has_answer = 0;
            
            if(Auth::user() && Auth::user()->isBusiness()){
            
                foreach($questions_answers as $questions_answer){
                    
                    if($questions_answer->user_id = Auth::user()->id){
                        $questions[$key]->has_answer = 1;
                        break;
                    }
                    
                }
            
            }
        
        } 


        $categories_lawyer = $this->categories_lawyer();
        
        return view('questions',['questions'=>$questions,'categories_lawyer'=>$categories_lawyer]);
    }
    
    public function questions_new()
    {

        $categories_lawyer = $this->categories_lawyer();

        return view('questions_new',['categories_lawyer'=>$categories_lawyer]);
    }
    
    
    
    public function questions_new_submit(Request $request)
    {
    
            $req = $request->all();
            
            $file = $request->file('file');
            
            if($file){
             
                $ext = array('png', 'jpg', 'jpeg', 'txt', 'xls', 'xlsx', 'doc');
                
                $file_ext = $file->getClientOriginalExtension();
                
                if(!in_array($file_ext,$ext)){
                        
                        $categories_lawyer = $this->categories_lawyer();
                        
                        return view('questions_new',['request'=>$request,'categories_lawyer'=>$categories_lawyer])
                        ->with('message', 'Не верный формат файла'); 
                        
                }else{
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                $file->move(public_path('files'),$fileName); 

                
                $File = new Files();
                
                $File->filename = $fileName;
                
                $File->save();
                
                $File_id = $File->id;
                
                }
            
            }else{
                
                $File_id = null;
                
            }

            
            $Question = new Questions();
            
            $Question->cat_id = $request->cat_id;
            $Question->name = $request->name;
            $Question->comment = $request->comment;
            $Question->question_from = $request->question_from;
            
           
            $Question->user_name = $request->user_name;
            $Question->user_email = $request->user_email;
            $Question->user_city = $request->user_city;

            $Question->file_id = $File_id;
            
            
            
            $Question->save();

            
            return view('questions_success');            
            
            
    
    }

/*
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
    

    }*/

}
