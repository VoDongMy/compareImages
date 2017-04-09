<?php
namespace App\Http\Controllers\Api;


use App\Models\Bids;
use App\Models\Category;
use App\Models\Items;
use App\Models\Likes;
use App\Models\History;
use App\Models\Pictures;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;


class NotificationController extends BaseController {

    public function __construct(Request $request) {
        parent::__construct($request);
    }

    public function getNotify(Request $request)
    {
        $rules = [
            // 'type'      => 'required|in:item'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ( $validator->passes() ) {
	        $user = $this->user;
	            if( empty($user))
	                return response()->json([
	                        'status_code' => 401,
	                        'messages'    => 'Unauthorized',
	                        'data'        => array()
	                        ],401);
	        $data = array(['id'=>1, 
		        	'user_id' => 2, 
		        	'title' => 'Notifications 1', 
		        	'descript' => 'abcdef', 
		        	'is_read'=>0, 
		        	'created_at'=> "2017-03-14 08:06:35",
	        		'updated_at'=> "2017-03-14 08:06:35"]);
	        return $this->response([
	                    'status_code' => 200,
	                    'messages'    => 'request success',
	                    'data'        => empty($data)? (object)[] : $data], 200);
	    }
	    return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function putPushNotification($type, Request $request)
    {
        $rules = [
            'type'      => 'required|in:0,1'
        ];
        $validator = Validator::make(array_merge($request->all(),['type' => $type]), $rules);
        if ( $validator->passes() ) {
	        $user = $this->user;
            if( empty($user))
                return response()->json([
                        'status_code' => 401,
                        'messages'    => 'Unauthorized',
                        'data'        => array()
                        ],401);
	        return $this->response([
	                    'status_code' => 200,
	                    'messages'    => 'request success',
	                    'data'        => pushNotification($type, $parameter = ['udid'=>$request->udid]) ], 200);
	    }
	    return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }
}