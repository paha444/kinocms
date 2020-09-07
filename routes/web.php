<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/verify/{token}', 'Auth\RegisterController@verify')->name('register.verify');


Route::get('/404', 'HomeController@error_404')->name('error_404');





//Route::get('/', 'HomeController@index')->name('index');


//Route::get('/blog', 'BlogController@blog')->name('blog_front');

Route::get('/', 'FilmsController@afisha')->name('index');
Route::get('film/{id}', 'FilmsController@film')->name('film');


///////////////////


Route::group(['prefix' => 'profile','namespace' => 'Profile'], function() {
    
    Route::group(['middleware' => 'role:client'], function() {
    
        Route::get('/', 'ProfileController@profile')->name('profile_index_client');

    });

});

/////////////////////


    Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function() {

        Route::group(['middleware' => 'role:admin'], function() {

            Route::get('/', 'FilmsController@films')->name('admin_index');


            Route::get('/films', 'FilmsController@films')->name('admin_films');

            //Route::get('/film/{id}', 'FilmsController@film')->name('admin_film');

            Route::get('/films/add', 'FilmsController@add')->name('admin_film_add');
            Route::get('/films/edit/{id}', 'FilmsController@edit')->name('admin_film_edit');
            Route::get('/films/delete/{id}', 'FilmsController@delete')->name('admin_film_delete');
            //Route::post('/pages/delete', 'PagesController@pages_delete')->name('pages_delete');

            Route::post('/films/add/submit', 'FilmsController@submit')->name('admin_film_add_submit');
            Route::post('/films/edit/{id}', 'FilmsController@edit_submit')->name('admin_film_edit_submit');
            //Route::post('/pages/delete/file','PagesController@page_delete_file')->name('page_delete_file');

        });
    });






