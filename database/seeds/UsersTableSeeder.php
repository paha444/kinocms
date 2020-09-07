<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        DB::table('users')->insert([
          'role_id' => 1,  
          'name' => 'admin',
          'email' => 'admin@gmail.com',
          'password' => bcrypt('admin'),
          'status' => 1,
        ]);

        DB::table('users')->insert([
          'role_id' => 2,  
          'name' => 'user',
          'email' => 'user@gmail.com',
          'password' => bcrypt('user'),
          'status' => 1,
        ]);
  
    }
}
