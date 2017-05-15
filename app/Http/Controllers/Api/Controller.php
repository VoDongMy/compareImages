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
                     // var_dump($this->compare($picture->thumbnail,$thumbnail));
                     
                    if (!$different_pixels) {
                        $dataPicture[] = array_merge($picture->toArray(),['ratio'=> 100 ]); 
                        //break;
                    } else {
                            $total = $sx1 * $sy1;
                            $ratio = number_format(100 * $different_pixels / $total, 2);
                            $compare = $this->compare($picture->thumbnail,$thumbnail);
                            if ($ratio <= 60 && $compare < 5){
                                $arrRatio = array();
                                switch ($compare) {
                                    case 0:
                                        $arrRatio = ['ratio' => 100];   
                                        break;

                                    case 2:
                                        $arrRatio = ['ratio' => rand(90,100)];   
                                        break;

                                    case 1:
                                        $arrRatio = ['ratio' => rand(90,100)];   
                                        break;

                                    case 4:
                                        $arrRatio = ['ratio' => rand(75,90)];   
                                        break;
                                    
                                    default:
                                        $arrRatio = ['ratio' => 100 - $ratio]; 
                                        break;
                                }
                                $dataPicture[] = array_merge($picture->toArray(),$arrRatio);
                            }
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

    public function PostCheckCompareImages(Request $request)
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
                     // var_dump($this->compare($picture->thumbnail,$thumbnail));
                     
                    if (!$different_pixels) {
                        $dataPicture[] = array_merge($picture->toArray(),['ratio'=> 100 ]); 
                    	//break;
                    } else {
                                $total = $sx1 * $sy1;
                                $ratio = number_format(100 * $different_pixels / $total, 2);
                                $compare = $this->compare($picture->thumbnail,$thumbnail);
                                $dataPicture[] = array_merge($picture->toArray(),$arrRatio = ['ratio' => $ratio, 'compare' => $compare]);
                        	    
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

    private function mimeType($i)
    {
        /*returns array with mime type and if its jpg or png. Returns false if it isn't jpg or png*/
        $mime = getimagesize($i);
        $return = array($mime[0],$mime[1]);
      
        switch ($mime['mime'])
        {
            case 'image/jpeg':
                $return[] = 'jpg';
                return $return;
            case 'image/png':
                $return[] = 'png';
                return $return;
            default:
                return false;
        }
    }  
    
    private function createImage($i)
    {
        /*retuns image resource or false if its not jpg or png*/
        $mime = $this->mimeType($i);
      
        if($mime[2] == 'jpg')
        {
            return imagecreatefromjpeg ($i);
        } 
        else if ($mime[2] == 'png') 
        {
            return imagecreatefrompng ($i);
        } 
        else 
        {
            return false; 
        } 
    }
    
    private function resizeImage($i,$source)
    {
        /*resizes the image to a 8x8 squere and returns as image resource*/
        $mime = $this->mimeType($source);
      
        $t = imagecreatetruecolor(8, 8);
        
        $source = $this->createImage($source);
        
        imagecopyresized($t, $source, 0, 0, 0, 0, 8, 8, $mime[0], $mime[1]);
        
        return $t;
    }
    
        private function colorMeanValue($i)
    {
        /*returns the mean value of the colors and the list of all pixel's colors*/
        $colorList = array();
        $colorSum = 0;
        for($a = 0;$a<8;$a++)
        {
        
            for($b = 0;$b<8;$b++)
            {
            
                $rgb = imagecolorat($i, $a, $b);
                $colorList[] = $rgb & 0xFF;
                $colorSum += $rgb & 0xFF;
                
            }
            
        }
        
        return array($colorSum/64,$colorList);
    }
    
        private function bits($colorMean)
    {
        /*returns an array with 1 and zeros. If a color is bigger than the mean value of colors it is 1*/
        $bits = array();
         
        foreach($colorMean[1] as $color){$bits[]= ($color>=$colorMean[0])?1:0;}

        return $bits;

    }
    
        public function compare($a,$b)
    {
        /*main function. returns the hammering distance of two images' bit value*/
        $i1 = $this->createImage($a);
        $i2 = $this->createImage($b);
        
        if(!$i1 || !$i2){return false;}
        
        $i1 = $this->resizeImage($i1,$a);
        $i2 = $this->resizeImage($i2,$b);
        
        imagefilter($i1, IMG_FILTER_GRAYSCALE);
        imagefilter($i2, IMG_FILTER_GRAYSCALE);
        
        $colorMean1 = $this->colorMeanValue($i1);
        $colorMean2 = $this->colorMeanValue($i2);
        
        $bits1 = $this->bits($colorMean1);
        $bits2 = $this->bits($colorMean2);
        
        $hammeringDistance = 0;
        
        for($a = 0;$a<64;$a++)
        {
        
            if($bits1[$a] != $bits2[$a])
            {
                $hammeringDistance++;
            }
            
        }
          
        return $hammeringDistance;
    }
}


