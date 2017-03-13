<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

class BaseController extends Controller
{
    function __contruct()
    {
        //
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