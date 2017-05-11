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
        $dataResponse = ['sever_name'  => 'DemoCompareImages', 
                         'version'     => '1.0.0',
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
            // 'image'      =>'required|mimes:jpeg,jpg,png', // 'max:200px',
            'notes'      =>'required' // 
            ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $user = $this->user;
            if (empty($user)) {
                return response()->json([
                        'status_code' => 401,
                        'messages'    => 'User token is invalid.',
                        'data'        => array()
                        ],401); 

            }
            if (is_array($request->images)) {
                $directory = 'data/images/';
                Upload::findOrCreateFolder($directory);
                foreach ($request->images as $key => $image) {
                    $filePath = Upload::uploadFile($image,$directory,md5(microtime()).'.'.$image->getClientOriginalName());
                    $picture = new Pictures;
                    $picture->url = $filePath;
                    $picture->thumbnail = Upload::cropImages($filePath,$directory,md5(microtime()).'_100_100.'.$image->getClientOriginalName(),100,100, $fit = null);
                    $picture->size = filesize($filePath);
                    $picture->user_id = $user->id;
                    $picture->notes = $request->notes;
                    $picture->save();    
                    $dataPicture[] = $picture;            
                }
                
                return $this->response([
                        'status_code' => 200,
                        'messages'    => 'request success',
                        'data'        => ['total'  => count($dataPicture), 
                        'items'     => $dataPicture]
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
