<?php

Route::group(['prefix' => 'api/'], function() {
    //
    Route::get('/', 'Controller@getDescription');

    /* API version 1.0.0
    *  Created by PhpStorm.
    *  User: tantq
    *  Date: 23/11/2015
    */
    Route::group(['prefix' => 'version/1.0/', 'namespace' => 'Api', 'as' => 'api.v1'], function() {

        //
        Route::get('/', 'Controller@getDescription');
        Route::get('test', 'UserController@test');
        Route::post('signup', 'UserController@postSignup');
        Route::post('update', 'UserController@postUpdate');
        Route::post('setting', 'UserController@setting');
        Route::post('delete', 'UserController@delete_account');
        Route::post('my_bids', 'UserController@get_my_bids');

        //
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


    /* API version 1.0.1
    *  User: tantq
    *  09/03/2017
    * 
    */
    Route::group(['prefix' => 'version/1.0.1/', 'namespace' => 'Api', 'as' => 'api.version.1-0-1', 'middleware' => 'auth.api'], function() {

        //Common API
        Route::get('/', 'Controller@getDescription');
        
        //User
        Route::post('/user/sign-up', 'UserController@postSignup');
        
        //Group Athenticate
        Route::group(['middleware' => 'auth.base.api'], function() {
            Route::post('/file/upload-images', 'Controller@postUploadImages');

            //User
            Route::put('/user/update', 'UserController@putUpdate');
            Route::get('/user/sign-out', 'UserController@getSignout');
            Route::put('/user/setting', 'UserController@setting');
            Route::delete('/user/delete/{id}', 'UserController@deleteAccount');
            Route::get('/user/my-bids', 'UserController@getMyBids');

            //Category
            Route::get('/categories', 'CategoryController@getCategoies');
            Route::post('/category/create', 'CategoryController@postCreate');
            Route::put('/category/update/{id}', 'CategoryController@putUpdateCreate');

            //Item
            Route::get('/item/detail/{id}', 'ItemController@getItemDetail');
            Route::get('/item/my-lists', 'ItemController@getlistMyItem');
            Route::post('/item/create', 'ItemController@postCreateItem');
            Route::put('/item/update/{id}', 'ItemController@putUpdateItem');
            Route::put('/item/like-unlike/{id}', 'ItemController@putLikeItem');
            Route::get('/item/lists', 'ItemController@getlistItem');

            //History
            Route::get('/history/{type}', 'HistoryController@getHistories');
            Route::put('/history/put-history/{type}', 'HistoryController@putHistories');
        });
    });
});