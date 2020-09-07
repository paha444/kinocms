<?php

namespace App\Http\Controllers\Profile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use Illuminate\Database\Eloquent\Model;

use App\Blog;

use Illuminate\Support\Facades\Auth;

use DB;

use File;

class BlogController extends Controller
{
    //
    
         public function blog_add(){
             return view('profile.blog_add');
         }

   






   
         public function blog(Request $request){

            $user = Auth::user();
            
            $blog = DB::table('blogs')->where('user_id', $user->id)->get();
                
            //$blog = Blog::all();

            return view('profile.blog', ['blogs' => $blog]);        

         }   
   
   
   
         public function submit(Request $req)
         {
            
            
            
            $user = Auth::user();
            
            //dd($req);
            
            
            
            $Post = new Blog();

            

            $file = $req->file('file');
            
            if($file){
            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('files'),$fileName); 

            
            }           


            $Post->name = $req->input('name');
            $Post->full_text = $req->input('full_text');

           // if(isset($fileName))
            //    $Offer->filename = $fileName;

            if(isset($fileName))
                $Post->filename = $fileName;


            $Post->link = $req->input('link');


                            
            $Post->user_id = $user->id;
            
            $Post->views = 0;
            
            $Post->save();
            
            
            $images = $req->images;
            
            if($images){    
            //while($i < count($req->input('images'))){
            foreach($images as $image){
            
                $sql[] = array(
                  'blog_id' => $Post->id,
                  'image_id' => $image,
                );
              //  $i++;
            }
            
            }
            
            
            DB::table('blogs_images')->insert($sql);
            
            
            
                        
            return redirect()->route('profile_blog_edit',$Post->id);
             
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
            
    
            $Post = Blog::find($id);

//$Offer->comment = '1111111';

//$Offer->save();
            
           
            //$Offer = new Offers();
            
            $Post->name = $req->input('name');
            $Post->full_text = $req->input('full_text');
           


            if(isset($fileName))
                $Post->filename = $fileName;


            $Post->link = $req->input('link');

            
            $Post->save();
            
            
            $images = $req->images;
            
            if($images){
                //while($i < count($req->input('images'))){
                foreach($images as $image){
                
                    $sql[] = array(
                      'blog_id' => $Post->id,
                      'image_id' => $image,
                    );
                  //  $i++;
                }
                
                
                DB::table('blogs_images')->insert($sql);
            }
 
 
            
            
                        
           return redirect()->route('profile_blog_edit',$id);
             
         }         
         



         public function edit($id)
         {
            
            $user = Auth::user();
            
            $Post = DB::table('blogs')->where('id', $id)->where('user_id', $user->id)->first();
            
            $post_images = DB::table('blogs_images')
            ->where('blogs_images.blog_id', $Post->id)
            ->join('images', 'images.id', '=', 'blogs_images.image_id')
            ->select('images.filename')
            ->get();
                        
            //dd($Post);
            //dd($post_images);
            
            return view('profile.blog_edit', ['post' => $Post,'post_images'=>$post_images]);     


         }

         public function blog_delete($id,$redirect=true)
         {
            
            $user = Auth::user();
            
            $Post = DB::table('blogs')->where('id', $id)->where('user_id', $user->id)->first();


            $filename = public_path('files').'/'.$Post->filename;
            
            //echo $image_path;
  
              if(File::exists($filename)) {
                    File::delete($filename);
              }

            $post_images = DB::table('blogs_images')
            ->where('blogs_images.blog_id', $Post->id)
            ->join('images', 'images.id', '=', 'blogs_images.image_id')
            ->select('images.filename')
            ->get();


            foreach($post_images as $image){
                
                DB::table('images')->where('filename', '=', $image->filename)->delete();

                
                $filename = public_path('images').'/'.$image->filename;
                
                  if(File::exists($filename)) {
                        File::delete($filename);
                  }
                
            }


            DB::table('blogs_images')->where('blog_id', '=', $Post->id)->delete();


            DB::table('blogs')->where('id', '=', $Post->id)->delete();


             if($redirect){
                return redirect()->route('profile_blog');
             }  
            

         }


         public function blogs_delete(Request $req)
         {

                if($req->delete){
                
                    foreach($req->delete as $value){
                    
                        $this->blog_delete($value,false);
                        
                    }
                
                }
                
                return redirect()->route('profile_blog');

         }  


        public function blog_delete_file(Request $request)
        {
            
            
            $Blog = Blog::where('filename', $request->filetodelete)->first();
            //$Offer = $Offer->where('image', $path);
            $Blog->filename = '';
            $Blog->save();
                        
            $image_path = public_path('files').'/'.$request->filetodelete;
            
            //echo $image_path;
  
              if(File::exists($image_path)) {
                    File::delete($image_path);
              }
            
        
        }

    
}
