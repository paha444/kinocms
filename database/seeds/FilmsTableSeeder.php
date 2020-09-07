<?php

use Illuminate\Database\Seeder;

class FilmsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $F = 1;
        
        while($F<=10){
        
            DB::table('films')->insert([
              'name' => 'Фильм '.$F,
              'intro_text' => 'Краткое описание фильма',
              'full_text' => 'Полное описание фильма',
              'image' => '159630499532724242081.jpg',
              'alias' => 'film_'.str_random(5),
              'meta_title' => 'Фильм '.$F,
              'meta_description' => 'Фильм '.$F,
              'status' => 1,
            ]);
            
            $F++;
        }
        
    }
}

