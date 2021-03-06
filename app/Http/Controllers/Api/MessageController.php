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
            $response = array();
            foreach ($data as $key => $groupChat) {
                $object = $groupChat->object;
                if (!empty($object)) {
                    switch ($object->type) 
                    {
                        case 1:
                            if ($user->id != $object->item_user_id && $object->status != 2) {
                                unset($data[$key]);
                            } else {
                                array_push($response, $groupChat);
                            }
                            break;

                        case 2:
                            if ($user->id != $object->item_user_id && $object->status != 2) {
                                unset($data[$key]);
                            } else {
                                array_push($response, $groupChat);
                            }
                            break;
                        
                        default:
                            array_push($response, $groupChat);
                            break;
                    }                
                }
            }
            return $this->response([
                        'status_code' => 200,
                        'messages'    => 'request success',
                        'data'        => empty($response)? [] : $response], 200);
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function getBoxMessages($id, Request $request)
    {
        $rules = [
            'id'      => 'required|numeric',
            'start_date_time'      => 'date_format:"Y-m-d H:i:s"',
            'quantity'      => 'numeric'
        ];
        $validator = Validator::make(array_merge($request->all(),['id'=>$id]), $rules);
        if ( $validator->passes() ) {
            $user = $this->user;
                if( empty($user))
                    return response()->json([
                            'status_code' => 401,
                            'messages'    => 'Unauthorized',
                            'data'        => array()
                            ],401);
            $listMessages = $this->message->getSelectMessageByBox($id);
            $data['total'] = $listMessages->count();
            $data['limit'] = empty($request->quantity)? 0 : $request->quantity;

            $listMessages = empty($request->start_date_time)? $listMessages : $listMessages->where('messages.created_at','<',$request->start_date_time);

            $listMessages = empty($request->quantity)? $listMessages : $listMessages->take($request->quantity);
            
            $data['items'] = $listMessages->orderBy('created_at', 'ASC')->get();
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

    public function deleteBoxMessages($id, Request $request)
    {
        $rules = [
            'id'      => 'required'
        ];
        $validator = Validator::make(array_merge($request->all(),['id'=>$id]), $rules);
        if ( $validator->passes() ) {
            try {
                $user = $this->user;
                if( empty($user))
                    throw new Exception("Unauthorized", 401);
                    
                $groupChat = GroupChat::with('messages')->find($id);
                if (empty($groupChat)) 
                    throw new \Exception("box chat id does not exist", 400);
                else {
                    Message::where('group_chat_id',$groupChat->id)->delete();
                    $groupChat->delete();
                }
            } catch (\Exception $e) {
                return response()->json([
                            'status_code' => $e->getCode(),
                            'messages'    => $e->getMessage(),
                            'data'        => (object)['delete' => false]
                            ],$e->getCode());
            }

            return $this->response([
                        'status_code' => 200,
                        'messages'    => 'request success',
                        'data'        => (object)['delete' => true]], 200);
	        
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
            'type'    => 'required|in:0,1,2,3',
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
                $data = $this->message->pushMessageToGroup($id, $parameter = ['userId' => $user->id,'type' => $request->type, 'content' => $request->content]);
                $deviceTokenUserInGroup = $this->groupChat->getDeviceToken($deviceType = 'ios', $id);

                $messages = ($request->type == 1)? $user->name . 'sent a photo.' : $request->content;
                sendiOSNotification($deviceTokenUserInGroup, $messages = $parameter['content'], ['type'=>1,'group_chat_id'=>$id, 'messages' => $messages, 'date_time'=>$data->created_at]);            
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
