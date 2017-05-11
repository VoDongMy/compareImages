<?php

Route::group(['prefix' => 'demo/'], function() {
    //
    Route::get('/', 'Controller@getDescription');
    // Route::group(['prefix' => 'version/1.0.1/', 'namespace' => 'Api', 'as' => 'api.version.1-0-1', 'middleware' => 'auth.api'], function() {
    Route::group(['prefix' => 'api/', 'namespace' => 'Api', 'as' => 'api.version.1-0-1'], function() {

        //Common API
        Route::get('/', 'Controller@getDescription');
        
        //User
        Route::post('/user/sign-up', 'UserController@postSignup');
        
        //Group Athenticate
        // Route::group(['middleware' => 'auth.base.api'], function() {
        Route::group(['middleware' => ''], function() {
            Route::post('/file/upload-images', 'Controller@postUploadImages');

            //User
            Route::put('/user/update', 'UserController@putUpdate');
            Route::get('/user/sign-out', 'UserController@getSignout');
            Route::put('/user/setting', 'UserController@setting');
            Route::get('/user/setting', 'UserController@getSetting');
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
            Route::delete('/item/delete/{id}', 'ItemController@deleteRemoveItem');
            Route::get('/item/lists', 'ItemController@getlistItem');

            //like/unlike
            Route::get('/item/like-lists', 'ItemController@getLikelistItems');
            Route::put('/item/like/{id}', 'ItemController@putLikeItem');
            Route::put('/item/unlike/{id}', 'ItemController@putUnLikeItem');
            
            //watch/disWatch
            Route::get('/item/watch-lists', 'ItemController@getWatchlistItems');
            Route::put('/item/watch/{id}', 'ItemController@putWatchItem');
            Route::put('/item/diswatch/{id}', 'ItemController@putUnWatchItem');

            //History
            Route::get('/history/{type}', 'HistoryController@getHistories');
            Route::put('/history/put-history/{type}', 'HistoryController@putHistories');

            //Bidding
            Route::post('/item/bidding/{id}', 'BidController@postBiddingItem');
            Route::put('/bidding/accept/{id}', 'BidController@putAcceptBiddingItem');

            //Exchange
            Route::post('/item/exchange/{id}', 'ExchangeController@postExchangeItem');
            Route::put('/exchange/accept/{id}', 'ExchangeController@putAcceptExchangeItem');

            //Notification
            Route::get('/notifications', 'NotificationController@getNotify');
            Route::put('/push-notification/{type}', 'NotificationController@putPushNotification');

            //Messages
            Route::get('/messages/lists', 'MessageController@getListMessages');
            Route::get('/messages/{id}', 'MessageController@getBoxMessages');
            Route::delete('/messages/remove/{id}', 'MessageController@deleteBoxMessages');
            Route::post('/messages/send/{id}', 'MessageController@postSendMessage');

        });
    });
});