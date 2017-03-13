<?php

namespace App\Http\Controllers\Api;

use Auth;
use App\Token;
use App\Helpers\Upload;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{

    // protected $user;

    // protected $token;

    public function __construct() {

        // authentication
        // if ($request->has('key') ) {
        //     $token = Token::whereKey($request['key'])->first();
        //     if ($token) {
        //         $this->token = $token;
        //     }
        //     // assign user
        //     $user = Token::userFor($request['key']);
        //     if ($user) {
        //         $this->user = $user;

        //         /*if (!Auth::check()) {
        //             Auth::loginUsingId($user->id);
        //         }*/
        //     }else{
        //         $this->user = '';
        //     }
        // }
    }


    public function getDescription()
    {
        $dataResponse = ['sever_name'  => 'Batr', 
                         'version'     => '1.0.1',
                         'language'    => 'php',
                         'database'    => 'mysql',
                         'description' => ''];
        return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $dataResponse
                    ], 200);    
    }

    public function PostUploadImages(Request $request)
    {
        if ($request->images) {
            if (is_array($request->images))
                foreach ($request->images as $key => $image) {
                    $dataImages[] = [ 'image_name'  => 'Batr_'.$key.'.png', 
                                      'directory_name'     => '1/images/2017/03/14/',
                                      'size' => '32Mb'
                                    ];
                }
                $dataResponse = ['total'  => count($dataImages), 
                                 'items'     => $dataImages];
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => 'invalid parameter',
                    'data'        => array()
                    ], 200);
    }
}
