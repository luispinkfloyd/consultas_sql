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

Route::get('/', function () {
    return redirect('/home');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::post('/database', 'HomeController@get_databases')->name('database');

Route::get('/schema', 'HomeController@get_schemas')->name('schema');

Route::get('/consulta', 'HomeController@consulta')->name('consulta');

Route::get('/export_excel', 'HomeController@export_excel')->name('export_excel');

Route::get('/export_sql', 'HomeController@export_sql')->name('export_sql');

Route::get('/ajax_get_consulta', 'HomeController@ajax_get_consulta')->name('ajax_get_consulta');

Route::get('/ajax_set_consulta', 'HomeController@ajax_set_consulta')->name('ajax_set_consulta');

//Route::post('/consulta', 'HomeController@consulta')->name('consulta');
