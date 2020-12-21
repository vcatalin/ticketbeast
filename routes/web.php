<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/orders/{confirmationNumber}', 'OrdersController@show');
Route::get('/concerts/{concertId}', 'ConcertsController@show')->name('concerts.show');
Route::post('/concerts/{concertId}/orders', 'ConcertOrdersController@store');

Route::post('/login', 'Auth\LoginController@login')->name('auth.login');
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('auth.show');
Route::post('/logout', 'Auth\LoginController@logout')->name('auth.logout');


Route::middleware(['auth'])->prefix('backstage')->namespace('Backstage')->group(function () {
    Route::get('/concerts/new', 'ConcertsController@create');

    Route::get('/concerts', 'ConcertsController@index');
    Route::post('/concerts', 'ConcertsController@store');
});
