<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Model;

use App\Blog;

use Illuminate\Support\Facades\Auth;

use DB;

use File;

class BlogController extends Controller
{
   

         public function add(){
            
             $blog_categories = DB::table('blog_categories')->where(['is_enabled'=>1])->get();
             
             return view('admin.blog_add',['blog_categories'=>$blog_categories]);
         }

   
   
         public function blog(Request $request){

            //$blog = Blog::all();

            $blog = DB::table('blogs')
            //->where('user_id', $user->id)
            ->leftJoin('users', 'users.id', '=', 'blogs.user_id')
            ->select('blogs.*','users.email as user_email')
            //->get();
            ->orderByDesc('blogs.id')
            ->paginate(20);

            return view('admin.blog', ['blogs' => $blog]);        

         }   
   
   
   
         public function submit(Request $req)
         {
            
            //$r = $req->all();
            
            $user = Auth::user();
            
            //dd($req);
            
            
            
            $Post = new Blog();

            
/*
            $file = $req->file('file');
            
            if($file){
            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('files'),$fileName); 

            
            } */           

            $Post->cat_id = $req->input('cat_id');

            $Post->status = $req->input('status');

            $Post->name = $req->input('name');
            $Post->name_en = $req->input('name_en');
            $Post->name_kz = $req->input('name_kz');
            $Post->full_text = $req->input('full_text');
            $Post->full_text_en = $req->input('full_text_en');
            $Post->full_text_kz = $req->input('full_text_kz');

            //if(isset($fileName))
            //    $Post->filename = $fileName;


            //$Post->link = $req->input('link');

            
            
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
                  'blog_id' => $Post->id,
                  'image_id' => $image,
                );
              //  $i++;
            }

            DB::table('blogs_images')->insert($sql);
            
            }
            
            
            
            
            
                        
            return redirect()->route('blog_edit',$Post->id);
             
         }   




         public function submit_edit(Request $req,$id)
         {
            
            $user = Auth::user();
            
            //$r = $req->all();
  
  //dd($id);
/*
            $file = $req->file('file');
            
            if($file){
            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('files'),$fileName); 

            
            }*/
            
            //dd($file);
            
    
            $Post = Blog::find($id);

//$Offer->comment = '1111111';

//$Offer->save();

            //print_r($r); die;
            $Post->cat_id = $req->input('cat_id');
            
            $Post->status = $req->input('status');
           
            //$Offer = new Offers();
            
            $Post->name = $req->input('name');
            $Post->name_en = $req->input('name_en');
            $Post->name_kz = $req->input('name_kz');
            $Post->full_text = $req->input('full_text');
            $Post->full_text_en = $req->input('full_text_en');
            $Post->full_text_kz = $req->input('full_text_kz');

          //  if(isset($fileName))
          //      $Post->filename = $fileName;


           // $Post->link = $req->input('link');

           
            
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
 
 
            
            
                        
           return redirect()->route('blog_edit',$id);
             
         }         
         



         public function edit($id)
         {
            
            $user = Auth::user();
            
            $Post = DB::table('blogs')->where('id', $id)->first();
            
            $post_images = DB::table('blogs_images')
            ->where('blogs_images.blog_id', $Post->id)
            ->join('images', 'images.id', '=', 'blogs_images.image_id')
            ->select('images.filename')
            ->get();
            
            $blog_categories = DB::table('blog_categories',['is_enabled'=>1])->get();
                        
            //dd($Post);
            //dd($post_images);
            
            return view('admin.blog_edit', ['post' => $Post,'post_images'=>$post_images,'blog_categories'=>$blog_categories]);     


         }


         public function blog_delete($id,$redirect=true)
         {
            
            $user = Auth::user();
            
            $Post = DB::table('blogs')->where('id', $id)->first();

            //if($Post->filename){
                //$filename = public_path('files').'/'.$Post->filename;
            //}
            //echo $image_path;
  
             // if(File::exists($filename)) {
             //       File::delete($filename);
             // }
             //print_r($Post); die;

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
                return redirect()->route('blog');
             }  
            

         }


         public function blogs_delete(Request $req)
         {

                if($req->delete){
                
                    foreach($req->delete as $value){
                    
                        $this->blog_delete($value,false);
                        
                    }
                
                }
                
                return redirect()->route('blog');

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




        public function blog_categories(){
            
            $blog_categories = DB::table('blog_categories')
            ->orderByDesc('id')
            ->paginate(20);  
            
            return view('admin.blog_categories',['blog_categories'=>$blog_categories]);
        }
        
        public function blog_categories_add(){

            $blog_categories = DB::table('blog_categories')->get();  
            
            return view('admin.blog_categories_add',['blog_categories'=>$blog_categories]);

            
        }

        public function blog_categories_add_submit(Request $request){
            
            $req = $request->all();
            //print_r($req);
            //die;
            
            DB::table('blog_categories')->insert([
            'parent_id' => $req['parent_id'],
            'link' => $req['link'],
            'is_enabled' => $req['is_enabled'],
            'name_ru' => $req['name_ru'],
            'name_en' => $req['name_en'],
            'name_kz' => $req['name_kz'],
            'full_text' => $req['full_text'],
            'full_text_en' => $req['full_text_en'],
            'full_text_kz' => $req['full_text_kz'],
            ]);
 
            return redirect()->route('blog_categories');
        
        }
        
        
        
        
         public function blog_categories_edit($id)
         {

            $user = Auth::user();
            
            $category = DB::table('blog_categories')->where('id', $id)
            //->where('user_id', $user->id)
            ->first();

            $blog_categories = DB::table('blog_categories')->get();  

            return view('admin.blog_categories_edit', ['blog_categories'=>$blog_categories,'category' => $category]);     


         }


        public function blog_categories_edit_submit(Request $request,$id){
            
            $req = $request->all();
            //print_r($req);
            //die;
            
            
            DB::table('blog_categories')
            ->where('id', $id)
            ->update([
            'parent_id' => $req['parent_id'],
            'link' => $req['link'],
            'is_enabled' => $req['is_enabled'],
            'name_ru' => $req['name_ru'],
            'name_en' => $req['name_en'],
            'name_kz' => $req['name_kz'],
            'full_text' => $req['full_text'],
            'full_text_en' => $req['full_text_en'],
            'full_text_kz' => $req['full_text_kz'],
            ]);
 
            return redirect()->route('blog_categories_edit',$id);
        
        }


         public function blog_category_delete($id,$redirect=true)
         {
            
            $user = Auth::user();

            DB::table('blog_categories')->where('id', $id)->delete();

             if($redirect){
                return redirect()->route('blog_categories');
             }

         }


         public function blog_categories_delete(Request $req)
         {
                
                if($req->delete){
                
                    foreach($req->delete as $value){
                    
                        $this->blog_category_delete($value,false);
                        
                    }
                
                }
                
                return redirect()->route('blog_categories');
                
         }        
















   
   
}
