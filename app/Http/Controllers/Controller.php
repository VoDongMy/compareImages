<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Token;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Auth;

abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    protected $user;

    protected $token;

    public function __construct(Request $request) {

        // authentication
        if ($request->has('key') ) {
            $token = Token::whereKey($request['key'])->first();
            if ($token) {
                $this->token = $token;
            }
            // assign user
            $user = Token::userFor($request['key']);
            if ($user) {
                $this->user = $user;

                /*if (!Auth::check()) {
                    Auth::loginUsingId($user->id);
                }*/
            }else{
                $this->user = '';
            }
        }

    }
}
