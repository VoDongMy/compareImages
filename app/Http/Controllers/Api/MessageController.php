<?php
namespace App\Http\Controllers\Api;


use App\Models\Bids;
use App\Models\Category;
use App\Models\Items;
use App\Models\Likes;
use App\Models\History;
use App\Models\Pictures;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;


class MessageController extends BaseController {

    protected $message;

    public function __construct(Request $request, Message $message) {
        parent::__construct($request);
        $this->message = $message;
    }

    public function getMessages(Request $request)
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

    public function postSendMessage($id, Request $request)
    {
        $rules = [
            'id'      => 'required|regex:/^([0-9]+,?)+$/',
            'content'      => 'required'
        ];
        $validator = Validator::make(array_merge($request->all(),['id' => $id]), $rules);
        if ( $validator->passes() ) {
	        $user = $this->user;
            if( empty($user))
                return response()->json([
                        'status_code' => 401,
                        'messages'    => 'Unauthorized',
                        'data'        => array()
                        ],401);
            $data = (object)[];
            try {
                $this->message->sendMessageToUser($user->id, $parameter = ['toUserId' => $id, 'content' => $request->content]);
            } catch (Exception $e) {
                
            }
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