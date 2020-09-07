<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Model;

use App\Pages;

use Illuminate\Support\Facades\Auth;

use DB;

use File;

class PagesController extends Controller
{



         public function add(){
             return view('admin.page_add');
         }

   
   
         public function pages(Request $request){

            //$pages = Pages::all();


            $pages = DB::table('pages')
            //->where('user_id', $user->id)
            ->join('users', 'users.id', '=', 'pages.user_id')
            ->select('pages.*','users.name as user_name')
            //->get();
            ->orderByDesc('pages.id')
            ->paginate(10);
            
            //print_r($pages); die;

            return view('admin.pages', ['pages' => $pages]);        

         }   
   
   
   
         public function submit(Request $req)
         {
            
            
            
            $user = Auth::user();
            
            //dd($req);
            
            
            
            $Post = new Pages();

            

            $file = $req->file('file');
            
            if($file){
            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('files'),$fileName); 

            
            }            


            $Post->name = $req->input('name');
            $Post->name_en = $req->input('name_en');
            $Post->name_kz = $req->input('name_kz');
            
            $Post->full_text = $req->input('full_text');
            $Post->full_text_en = $req->input('full_text_en');
            $Post->full_text_kz = $req->input('full_text_kz');
            
            $Post->menu_id = $req->input('menu_id');



            if(isset($fileName))
                $Post->filename = $fileName;


            $Post->link = $req->input('link');


           // if(isset($fileName))
            //    $Offer->filename = $fileName;
                            
            $Post->user_id = $user->id;
            
            $Post->views = 0;
            
            $Post->save();
            
            
            $images = $req->images;
            
            if($images){    
            //while($i < count($req->input('images'))){
            foreach($images as $image){
            
                $sql[] = array(
                  'page_id' => $Post->id,
                  'image_id' => $image,
                );
              //  $i++;
            }

            DB::table('pages_images')->insert($sql);
            
            }
            
            
            
            
            
                        
            return redirect()->route('page_edit',$Post->id);
             
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
            
    
            $Post = Pages::find($id);

//$Offer->comment = '1111111';

//$Offer->save();
            
           
            //$Offer = new Offers();
            
            $Post->name = $req->input('name');
            $Post->name_en = $req->input('name_en');
            $Post->name_kz = $req->input('name_kz');

            $Post->full_text = $req->input('full_text');
            $Post->full_text_en = $req->input('full_text_en');
            $Post->full_text_kz = $req->input('full_text_kz');

            if(isset($fileName))
                $Post->filename = $fileName;


            $Post->link = $req->input('link');

            $Post->menu_id = $req->input('menu_id');
            
            $Post->save();
            
            
            $images = $req->images;
            
            if($images){
                //while($i < count($req->input('images'))){
                foreach($images as $image){
                
                    $sql[] = array(
                      'page_id' => $Post->id,
                      'image_id' => $image,
                    );
                  //  $i++;
                }
                
                
                DB::table('pages_images')->insert($sql);
            }
 
 
            
            
                        
           return redirect()->route('page_edit',$id);
           //return redirect()->route('pages',$id);
             
         }         
         



         public function edit($id)
         {
            
            $user = Auth::user();
            
            $Post = DB::table('pages')->where('id', $id)->where('user_id', $user->id)->first();
            
            $post_images = DB::table('pages_images')
            ->where('pages_images.page_id', $Post->id)
            ->join('images', 'images.id', '=', 'pages_images.image_id')
            ->select('images.filename')
            ->get();
                        
            //dd($Post);
            //dd($post_images);
            
            return view('admin.page_edit', ['post' => $Post,'post_images'=>$post_images]);     


         }


         public function page_delete($id,$redirect=true)
         {
            
            $user = Auth::user();
            
            $Post = DB::table('pages')->where('id', $id)->where('user_id', $user->id)->first();


            $filename = public_path('files').'/'.$Post->filename;
            
            //echo $image_path;
  
              if(File::exists($filename)) {
                    File::delete($filename);
              }

            $post_images = DB::table('pages_images')
            ->where('pages_images.page_id', $Post->id)
            ->join('images', 'images.id', '=', 'pages_images.image_id')
            ->select('images.filename')
            ->get();


            foreach($post_images as $image){
                
                DB::table('images')->where('filename', '=', $image->filename)->delete();

                
                $filename = public_path('images').'/'.$image->filename;
                
                  if(File::exists($filename)) {
                        File::delete($filename);
                  }
                
            }


            DB::table('pages_images')->where('page_id', '=', $Post->id)->delete();


            DB::table('pages')->where('id', '=', $Post->id)->delete();


             if($redirect){
                return redirect()->route('pages');
             }  
            

         }


         public function pages_delete(Request $req)
         {

                if($req->delete){
                
                    foreach($req->delete as $value){
                    
                        $this->page_delete($value,false);
                        
                    }
                
                }
                
                return redirect()->route('pages');

         }   

        public function page_delete_file(Request $request)
        {
            
            
            $Page = Pages::where('filename', $request->filetodelete)->first();
            //$Offer = $Offer->where('image', $path);
            $Page->filename = '';
            $Page->save();
                        
            $image_path = public_path('files').'/'.$request->filetodelete;
            
            //echo $image_path;
  
              if(File::exists($image_path)) {
                    File::delete($image_path);
              }
            
        
        }




}
