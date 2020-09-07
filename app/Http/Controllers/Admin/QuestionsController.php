<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use DB;

use Illuminate\Support\Str;

class QuestionsController extends Controller
{
    
         public function questions(Request $request){

            //$blog = Blog::all();

            $questions = DB::table('questions')
            //->where('user_id', $user->id)
            ->leftJoin('users', 'users.id', '=', 'questions.user_id')
            ->select('questions.*','users.email as user_email')
            //->get();
            ->paginate(10);
            

            foreach($questions as $key=>$question){
            
            
                $questions_answers = DB::table('questions_answers')
                ->where('questions_answers.questions_id', $question->id)
                ->get()->toArray();
            
                $questions[$key]->answers = $questions_answers;
            
            } 
            //print_r($questions);

            return view('admin.questions', ['questions' => $questions]);        

         }   
   
 


         public function question_edit_submit(Request $req,$id)
         {
            


            DB::table('questions')->where('id', $id)->update([
            'is_enabled' => $req->is_enabled,
            'name' => $req->name,
            'comment' => $req->comment,
            ]);

            
            
                        
           return redirect()->route('questions',$id);
             
         }         
         



         public function question_edit($id)
         {
            
            $user = Auth::user();
            
            $question = DB::table('questions')->where('id', $id)->first();
            
            
            return view('admin.question_edit', ['question' => $question]);     


         }


         public function question_delete($id,$redirect=true)
         {
            


            DB::table('questions')->where('id', '=', $id)->delete();


             if($redirect){
                return redirect()->route('questions');
             }  
            

         }


         public function questions_delete(Request $req)
         {

                if($req->delete){
                
                    foreach($req->delete as $value){
                    
                        $this->question_delete($value,false);
                        
                    }
                
                }
                
                return redirect()->route('questions');

         }   
         
         
         
         
         
         
         public function question_answer_edit_submit(Request $req,$id)
         {
            
            //$user = Auth::user();
            
            
            DB::table('questions_answers')->where('id', $id)->update([
            'is_enabled' => $req->is_enabled,
            'comment' => $req->comment,
            ]);
            
            
                        
           return redirect()->route('questions',$id);
             
         }         
         



         public function question_answer_edit($id)
         {
            
            $user = Auth::user();
            

            $question_answer = DB::table('questions_answers')->where('id', $id)->first();
            
            
            return view('admin.question_answer_edit', ['question_answer' => $question_answer]);  
            
            //return view('admin.question_answer_edit', ['post' => $Post,'post_images'=>$post_images,'blog_categories'=>$blog_categories]);     


         }


         public function question_answer_delete($id,$redirect=true)
         {
            
            DB::table('questions_answers')->where('id', '=', $id)->delete();


             //if($redirect){
                return redirect()->route('questions');
             //}   
            

         }         
         
         
         
         
    
}
