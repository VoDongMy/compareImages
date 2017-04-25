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
use App\Models\GroupChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;


class MessageController extends BaseController {

    protected $message;
    protected $groupChat;

    public function __construct(Request $request, Message $message, GroupChat $groupChat) {
        parent::__construct($request);
        $this->message = $message;
        $this->groupChat = $groupChat;
    }

    public function getListMessages(Request $request)
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
	        $data = $this->groupChat->getListGroupByUserId($user->id);
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
            $data = (object)[];
            $messages = 'request success';
	        $user = $this->user;
            if( empty($user))
                return response()->json([
                        'status_code' => 401,
                        'messages'    => 'Unauthorized',
                        'data'        => array()
                        ],401);
            $data = (object)[];
            try {

                $data = $this->message->pushMessageToGroup($id, $parameter = ['userId' => $user->id, 'content' => $request->content]);
                // $data = $this->message->sendMessageToUser($user->id, $parameter = ['toUserId' => $id, 'content' => $request->content]);
            } catch (Exception $e) {
                $messages = $e->getMessage();
            }
	        return $this->response([
	                    'status_code' => 200,
	                    'messages'    => $messages,
	                    'data'        => $data], 200);
	    }
	    return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }
}