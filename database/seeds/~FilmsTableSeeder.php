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
        
        DB::table('films')->insert([
          'name' => 'Фильм 1',
          'email' => str_random(10).'@gmail.com',
          'password' => bcrypt('admin'),
          'status' => 1,
        ]);
        
    }
}


            $table->text('intro_text')->nullable();
            $table->text('full_text')->nullable();
            $table->string('image')->nullable();
            
            $table->string('alias')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();