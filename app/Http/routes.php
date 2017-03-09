<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'UserController@test');
Route::group(['prefix' => 'api/v1'], function() {
    //Route::controller('user', 'UserController');
    Route::get('test', 'UserController@test');
    Route::post('signup', 'UserController@postSignup');
    Route::post('update', 'UserController@postUpdate');
    Route::post('setting', 'UserController@setting');
    Route::post('delete', 'UserController@delete_account');
    Route::post('my_bids', 'UserController@get_my_bids');


    Route::post('wishlist', 'UserController@wishlist');
    Route::post('wishlist/add', 'UserController@add_wishlist');
    Route::post('wishlist/remove', 'UserController@remove_wishlist');

    Route::post('listing', 'UserController@listing');
    Route::post('listing/remove', 'ItemController@remove_item');

    Route::post('category', 'CategoryController@index');
    Route::post('item/create', 'ItemController@create');
    Route::post('items', 'ItemController@show');
    Route::post('items/finding', 'ItemController@finding');
    Route::post('item/like', 'ItemController@like_item');
    Route::post('item/dislike', 'ItemController@dislike_item');


    Route::post('bidding/create', 'ItemController@bidding_item');
    Route::post('bidding/list', 'ItemController@get_bids');
    Route::post('bidding/bid_status', 'ItemController@update_status_bid');


});
