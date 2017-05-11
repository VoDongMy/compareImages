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
        Route::group(['middleware' => 'auth.base.api'], function() {
            Route::post('/file/upload-images', 'Controller@postUploadImages');

         

        });
    });
});