<?php

namespace App\Helpers;

use Folklore\Image\Facades\Image;

class Upload {

    // ACL flags
    // define("ACL_PRIVATE", "private");
    // define("ACL_PUBLIC_READ", "public-read");
    // define("ACL_PUBLIC_READ_WRITE", "public-read-write");

    /**
     * Returns an excerpt from a given string (between 0 and passed limit variable).
     *
     * @param $string
     * @param int $limit
     * @param string $suffix
     * @return string
     */
    public static function uploadFileS3($filepath,$keyname,$acl='public-read',$contentType='')
    {
        $s3 = \AWS::createClient('s3');
        try {
            $object = array(
                'Bucket'     => env('BUCKET_NAME', 'awsAccessKey'),
                'Key'        => $keyname,
                'SourceFile' => $filepath,
                'ACL'  => $acl
            );
            if (!empty($contentType)) {
                $object = array_merge($object,['ContentType'=>$contentType]);
            }
            $s3->putObject($object);
        } catch (Exception $e) {
            return FALSE;
        }
        return $s3->getObjectUrl(env('BUCKET_NAME', 'awsAccessKey'), $keyname);
    }

    /**
     * @author Khiem Le <khiem.lv@neo-lab.vn>
     * @param $key
     * @param $savePath
     * @return bool
     */
    public static function getObject($key, $savePath = ''){
        $s3 = \AWS::createClient('s3');
        $bucket = env('BUCKET_NAME');
        try {
            if(!empty($savePath)){
                $result = $s3->getObject(array(
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'SaveAs' => $savePath
                ));
            } else {
                $result = $s3->getObject(array(
                    'Bucket' => $bucket,
                    'Key' => $key
                ));
            }
            return $result;
        } catch (\Exception $e){
            return false;
        }
    }

    /**
     * @param $file
     * @param $uploadPath
     * @param $fileName
     * @return bool|string
     */
    public static function uploadFile($file,$uploadPath,$fileName)
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = $fileName? $fileName : basename($file->getClientOriginalName(), '.'.$extension);
        $file->move($uploadPath, $fileName);
        if (file_exists($uploadPath.'/'.$fileName)) {
            return $uploadPath.'/'.$fileName;
        }
        return FALSE;
    }

    public static function cropImages($filePath,$uploadPath,$fileName,$h,$w,$fit = null)
    {
        $source = $filePath;
        $destination = $uploadPath.'/'.$fileName;
        $width  = $w;
        $height = $h;
        $imagine   = new \Imagine\Gd\Imagine();
        $size      = new \Imagine\Image\Box($width, $height);
        $mode      = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
        $resizeimg = $imagine->open($source)
                        ->thumbnail($size, $mode);
        $sizeR     = $resizeimg->getSize();
        $widthR    = $sizeR->getWidth();
        $heightR   = $sizeR->getHeight();

        $preserve  = $imagine->create($size);
        $startX = $startY = 0;
        if ( $widthR < $width ) {
            $startX = ( $width - $widthR ) / 2;
        }
        if ( $heightR < $height ) {
            $startY = ( $height - $heightR ) / 2;
        }
        $preserve->paste($resizeimg, new \Imagine\Image\Point($startX, $startY))
            ->save($destination);
        return $uploadPath.'/'.$fileName;
    }

    public static function findOrCreateFolder($path)
    {
        if (!is_dir($path)) {
            if (!@mkdir($path, 0777, true)) {
                return FALSE;
            }
            return TRUE;
        }
    }
}