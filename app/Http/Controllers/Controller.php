<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getDescription()
    {
        $res = ['status' => 200,
                'messages' => 'request success',
                'sever_name'=>'CompareImages', 
                'language' => 'php',
                'database' => 'mysql',
                'version' => '',
                'description' => ''];
        dd($res);        
    }


}
