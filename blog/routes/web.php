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
    return view('welcome');
});

Route::get('/addUser','Controller@signUp');
Route::post('/addUser','Controller@signUp');
Route::get('/login','Controller@signIn');
Route::post('/login','Controller@signIn');
Route::get('/dashboard','Controller@dashboard');
Route::post('/update-user','Controller@updateUser');
Route::get('/paypal','Controller@paypalInstant');
Route::get('/payment/success','Controller@paymentSuccess');
Route::get('/logout','Controller@logout');