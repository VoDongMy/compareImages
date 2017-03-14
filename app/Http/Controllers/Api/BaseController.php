<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\UserToken;

abstract class BaseController extends Controller
{
    protected $user;

    protected $token;

    public function __construct(Request $request)
    {
        if ($request->header('User-Token')) {
            $this->token = $token = UserToken::with('user')->where('key',$request->header('User-Token'))->first();
            if ($token)
                $this->user = $token->user;
            else
               $this->user = (object)['id' => 0]; 
        }
    }

    /**
     * default reponse format
     * @param  [type]  $dataResponse [description]
     * @param  integer $code [description]
     * @return [type]        [description]
     */
    protected function response($dataResponse = array(), $code = 200)
    {
        return response()->json($dataResponse, $code, []);
    }
}