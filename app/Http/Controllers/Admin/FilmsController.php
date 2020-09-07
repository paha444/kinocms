<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use App\Models\Admin\Films;

use DB;

use File;


class FilmsController extends Controller
{
    

         public function films(){
        
            $films = DB::table('films')
            ->orderByDesc('films.id')
            ->paginate(20);
            
            return view('admin.films', ['films' => $films]);        

         }
    
         public function add(){
             return view('admin.film_add');
         }


         public function submit(Request $req)
         {
            
            //$user = Auth::user();
          
            $Post = new Films();

            $file = $req->file('image');
            
            if($file){
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                $file->move(public_path('images/films'),$fileName); 
            }            


            $Post->name = $req->input('name');
            $Post->intro_text = $req->input('intro_text');
            $Post->full_text = $req->input('full_text');

            if(isset($fileName))
                $Post->image = $fileName;
            
            $Post->youtube = $req->input('youtube');
            $Post->alias = $req->input('alias');
            $Post->meta_title = $req->input('meta_title');
            $Post->meta_description = $req->input('meta_description');
            $Post->status = $req->input('status');

            $Post->save();
            
            return redirect()->route('admin_film_edit',$Post->id);
             
         }   



         public function edit_submit(Request $req,$id)
         {
            
            //$user = Auth::user();
            
            $Post = Films::find($id);

            $file = $req->file('image');
            
            if($file){
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                $file->move(public_path('images/films'),$fileName); 
            }            


            $Post->name = $req->input('name');
            $Post->intro_text = $req->input('intro_text');
            $Post->full_text = $req->input('full_text');

            if(isset($fileName))
                $Post->image = $fileName;
            
            $Post->youtube = $req->input('youtube');
            $Post->alias = $req->input('alias');
            $Post->meta_title = $req->input('meta_title');
            $Post->meta_description = $req->input('meta_description');
            $Post->status = $req->input('status');

            $Post->save();
            
            return redirect()->route('admin_film_edit',$Post->id);
             
         }   



         public function edit($id)
         {
            
            
            $Film = DB::table('films')->where('id', $id)->first();
            
            return view('admin.film_edit', ['Film' => $Film]);     


         }


         public function delete($id)
         {
            
            //$user = Auth::user();
            
            $Film = DB::table('films')->where('id', $id)->first();


            $filename = public_path('images/films').'/'.$Film->image;
            
              if(File::exists($filename)) {
                    File::delete($filename);
              }


            DB::table('films')->where('id', '=', $Film->id)->delete();


          
                return redirect()->route('admin_films');
        
            

         }

    
    
    
    
}
