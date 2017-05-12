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
                $directory = 'data/images';
                Upload::findOrCreateFolder($directory);
                foreach ($request->images as $key => $image) {
                    $filePath = Upload::uploadFile($image,$directory,md5(microtime()).'.'.str_replace(' ', '_', $image->getClientOriginalName()));
                    $picture = new Pictures;
                    $picture->url = $filePath;
                    $picture->thumbnail = Upload::cropImages($filePath,$directory,md5(microtime()).'_100_100.'.str_replace(' ', '_', $image->getClientOriginalName()),100,100, $fit = null);
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

    public function PostCompareImages(Request $request)
    {

        $rules = [
            // 'image'      =>'required|mimes:jpeg,jpg,png', // 'max:200px',
            // 'notes'      =>'required' // 
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
            $dataPicture = array();
            if (is_array($request->images)) {
                $directory = 'data/images/tmp';
                Upload::findOrCreateFolder($directory);
                foreach ($request->images as $key => $image) {
                    $filePath = Upload::uploadFile($image,$directory,md5(microtime()).'.'.str_replace(' ', '_', $image->getClientOriginalName()));
                    $thumbnail = Upload::cropImages($filePath,$directory,md5(microtime()).'_100_100.'.str_replace(' ', '_', $image->getClientOriginalName()),100,100, $fit = null);
                    
                    break;            
                }
                $pictures = Pictures::get();
                foreach ($pictures as $key => $picture) {
                    // create images
                    $i1 = @imagecreatefromstring(file_get_contents(url($thumbnail)));
                    $i2 = @imagecreatefromstring(file_get_contents($picture->thumbnail));
                     
                    // dimensions of the first image
                    $sx1 = imagesx($i1);
                    $sy1 = imagesy($i1);
                     
                    // create a diff image
                    $diffi = imagecreatetruecolor($sx1, $sy1);
                    $green = imagecolorallocate($diffi, 0, 255, 0);
                    imagefill($diffi, 0, 0, imagecolorallocate($diffi, 0, 0, 0));
                     
                    // increment this counter when encountering a pixel diff
                    $different_pixels = 0;
                     
                    // loop x and y
                    for ($x = 0; $x < $sx1; $x++) {
                        for ($y = 0; $y < $sy1; $y++) {
                            $rgb1 = imagecolorat($i1, $x, $y);
                            $pix1 = imagecolorsforindex($i1, $rgb1);
                     
                            $rgb2 = imagecolorat($i2, $x, $y);
                            $pix2 = imagecolorsforindex($i2, $rgb2);
                     
                            if ($pix1 !== $pix2) { // different pixel
                                // increment and paint in the diff image
                                $different_pixels++;
                                imagesetpixel($diffi, $x, $y, $green);
                            }
                     
                        }
                    }
                     
                     
                    if (!$different_pixels) {
                        $dataPicture[] = array_merge($picture->toArray(),['ratio'=> 100 ]); 
                    } else {
                        $total = $sx1 * $sy1;
                        $ratio = number_format(100 * $different_pixels / $total, 2);
			//var_dump($ratio);
                        if ($ratio <= 60)
                            $dataPicture[] = array_merge($picture->toArray(),['ratio'=> 100-$ratio ]);
                    }
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
                    'data'        => (object)[]
                    ], 200);
    }
}

