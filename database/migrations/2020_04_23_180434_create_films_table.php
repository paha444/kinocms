<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('films', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name')->nullable();
            $table->text('intro_text')->nullable();
            $table->text('full_text')->nullable();
            $table->string('image')->nullable();
            $table->string('youtube')->nullable();
            
            $table->string('alias')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            
            $table->integer('views')->default(0)->unsigned();
            $table->boolean('status')->default(0)->unsigned();

            $table->timestamps();
        });
        
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('films');
    }
}
