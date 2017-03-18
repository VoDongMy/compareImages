<?php

namespace App\Http\Controllers\Api;

use Auth;
use App\Token;
use App\Helpers\Upload;
use App\Models\Pictures;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    public function __construct(Request $request) {
        parent::__construct($request);
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

        $rules = [
            // 'images.*'      =>'required|mimes:jpeg,jpg,png' // 'max:200px'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $user = $this->user;
            if (empty($user)) {
                $messages['error'] = 'User token is invalid.';
                return response()->json([
                    'status' => false,
                    'data' => $messages
                ], 200);
            }
            if (is_array($request->images)) {
                $directory = 'data/'.$user->id.'/images/'.date('Y/m/d');
                Upload::findOrCreateFolder($directory);
                foreach ($request->images as $key => $image) {
                    $picture = new Pictures;
                    $picture->url = Upload::uploadFile($image,$directory,md5(microtime()).'.'.$image->getClientOriginalName());
                    $picture->thumbnail = Upload::cropImages($image,$directory,md5(microtime()).'_100_100.'.$image->getClientOriginalName(),100,100);
                    $picture->size = filesize($image);
                    $picture->item_id = 0;
                    $picture->save();                     }
                $dataResponse = ['total'  => count($dataImages), 
                                'items'     => $dataImages = 1];
                return $this->response([
                        'status_code' => 200,
                        'messages'    => 'request success',
                        'data'        => $dataResponse
                        ], 200);               
            }
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 200);
    }
}
