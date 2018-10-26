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

Route::get('/', 'DiscountsController@upload');
Route::post('/discounts', 'DiscountsController@showResult')->name('discount.filter');
Route::post('/api/discounts', 'DiscountsController@applyDiscount');