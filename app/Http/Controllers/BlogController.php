<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Illuminate\Database\Eloquent\Model;

use App\Blog;

use App\Pages;

use DB;

class BlogController extends Controller
{
    //
/*
         static function partners($ids=array()){
            
            
            $partners = DB::table('blogs')
            
            ->join('blog_categories', function ($join) use($cat){
                $join->on('blog_categories.id', '=', 'blogs.cat_id')
                     ->where('blog_categories.id','=', 9);
            })                

            ->where(['id'=>$id,'status'=>1])->first();
            
 

        return $partners;

   }*/


         public function search(Request $request){

                $blog = DB::table('blogs')

                
                //->where('blogs.link','=',$request->rubr) 
                ->where('blogs.name','LIKE','%'.$request->v.'%') 
                ->orWhere(function($query) use($request) {
                      $query->Where('blogs.intro_text','LIKE','%'.$request->v.'%') 
                            ->orWhere('blogs.full_text','LIKE','%'.$request->v.'%');
                })
                
                
                ->where('blogs.status','=',1)
                ->where('blogs.cat_id','!=',9)
                
                ->groupBy('blogs.id')
                ->orderByDesc('blogs.id')
                
                //->select('blogs.*')
                
                ->paginate(10);

           foreach($blog as $key=>$post){


                $blog_images = DB::table('blogs_images')
                ->where('blogs_images.blog_id', $post->id)
                ->join('images', 'images.id', '=', 'blogs_images.image_id')
                ->select('images.filename')
                ->get()->toArray();

                $blog[$key]->images = $blog_images;

           }
        
        //dd($blog);
            //$Pages = Pages::all();

            return view('search', ['blog' => $blog,'request'=>$request]);        


        }


         public function page($id){


            $Page = DB::table('blogs')->where(['id'=>$id,'status'=>1])->first();
            $Page_images = '';
            
            if($Page){
                $Page_images = DB::table('blogs_images')
                ->where('blogs_images.blog_id', $Page->id)
                ->join('images', 'images.id', '=', 'blogs_images.image_id')
                ->select('images.filename')
                ->get();
            }

            //dd($Offer);
            //dd($offers_images);
            
            $Pages = Pages::all();
            
            $blog_categories = DB::table('blog_categories')->where(['is_enabled'=>1])->where('id','!=',9)->get();
            

            return view('blog_page', ['Page' => $Page,'page_images'=>$Page_images,'pages'=>$Pages,'blog_categories'=>$blog_categories]);

        }



         public function blog(Request $request,$cat=''){


            
            //$blog = Blog::all();
            //print_r($request->all()); die;
            
            
            $blog_categories = DB::table('blog_categories')->where(['is_enabled'=>1])->where('id','!=',9)->get();
            
            //print_r($blog_categories);
            
            if(empty($cat)){

                //$blog = DB::table('blogs')->where(['status'=>1])->orderByDesc('id')->paginate(10);

                $blog = DB::table('blogs')
                //->join('blog_categories', 'images.id', '=', 'blogs_images.image_id')
                
                //->leftjoin('blog_categories', function ($join){
                //    $join->on('blog_categories.id', '=', 'blogs.cat_id');
                //         ->where('blog_categories.id','!=', 9);
                    
                //})                
                
                
                
                ->where('blogs.status','=',1)
                ->where('blogs.cat_id','!=',9)
                
                
                ->orderByDesc('blogs.id')
                ->groupBy('blogs.id')
                ->paginate(10);
                
                //echo 1;
                
            
            }else{

                $blog = DB::table('blogs')
                //->join('blog_categories', 'images.id', '=', 'blogs_images.image_id')
                
                ->leftjoin('blog_categories', function ($join) use($cat){
                    $join->on('blog_categories.id', '=', 'blogs.cat_id')
                         ->where('blog_categories.link','=', $cat);
                //         ->where('blog_categories.id','!=', 9);
                })                
                
                
                ->where('blogs.status','=',1)
                ->where('blogs.cat_id','!=',9)
                
                ->groupBy('blogs.id')
                ->orderByDesc('blogs.id')
                
                ->select('blogs.*')
                
                ->paginate(10);
            

            }
            
            
            //print_r($blog);
            
            //foreach ($products as $product) {
            //    echo $product->name;
            //}
            
            /*
                        $blog = DB::table('offers')
            
                        ->where($where)
                        ->join('users', 'users.id', '=', 'offers.user_id')
            
                        ->select('offers.*','users.name')
            
                        ->get();
            */

           foreach($blog as $key=>$post){


                $blog_images = DB::table('blogs_images')
                ->where('blogs_images.blog_id', $post->id)
                ->join('images', 'images.id', '=', 'blogs_images.image_id')
                ->select('images.filename')
                ->get()->toArray();

                $blog[$key]->images = $blog_images;

           }
        
        //dd($blog);
            $Pages = Pages::all();

            return view('blog', ['blog' => $blog,'pages'=>$Pages,'blog_categories'=>$blog_categories]);        

         }
         



}
