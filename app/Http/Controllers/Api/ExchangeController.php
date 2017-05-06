<?php

namespace App\Http\Controllers\Api;


use App\Models\Bids;
use App\Models\Exchanges;
use App\Models\Category;
use App\Models\Items;
use App\Models\Likes;
use App\Models\History;
use App\Models\Pictures;
use App\Models\User;
use App\Models\Watch;
use App\Models\GroupChat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use App\Helpers\DataLog;
use Exception;



class ExchangeController extends BaseController{

    private $items;
    private $history;
    private $message;

    public function __construct(Request $request, Items $items, History $history, Message $message) {
        parent::__construct($request);
        $this->items = $items;
        $this->history = $history;
        $this->message = $message;
    }


    public function postExchangeItem($id, Request $request)
    {
        $rules = [
                'id'=>'required|regex:/^[0-9]+$/',
                'item_exchange_id'=>'required|regex:/^[0-9]+$/',
        ];
        $validator = Validator::make(array_merge($request->all(),['id'=>$id]), $rules);
        if ( $validator->passes() ) {
        	$item = (object)[];
        	$messages = 'request success';
        	try {
        		$user = $this->user;
        		if(empty($user))
                return response()->json([
                    'status_code' => 401,
                    'messages'    => 'Unauthorized',
                    'data'        => array()
                    ],401);  

                $item = Items::with(array('user'=>function($query){
                            $query->leftJoin('user_tokens', 'users.id', '=', 'user_tokens.user_id')->select('users.id','user_tokens.device_token');
                        }))->find($id);

	            $item_exchange = Items::with(array('user'=>function($query){
	                        $query->leftJoin('user_tokens', 'users.id', '=', 'user_tokens.user_id')->select('users.id','user_tokens.device_token');
	                    }))->where('user_id', $user->id)->find($request->item_exchange_id);
                if( empty($item))
                    throw new Exception("Item not found.", 400);

	            if( empty($item_exchange))
                    throw new Exception("item exchange not found.", 400);

	            $exchange = Exchanges::where('user_id',$user->id)->where('item_id',$id)->where('item_exchange_id',$request->item_exchange_id)->first();
	            if(empty($exchange))
	                $exchange = new Exchanges();
	            $exchange->user_id = $user->id;
	            $exchange->item_id = $id;
	            $exchange->item_exchange_id = $request->item_exchange_id;
	            $exchange->status = 1;
	            if ($exchange->save()) {
	                //$notifySetting
	            	$messages = 'New exchange item ' . $item->title . ' vs ' . $item_exchange->title;

	                $this->message->pushExchangeMessageToUser($user->id, $parameter = ['exchangeId' => $exchange->id, 'content' => $messages]);
	                sendiOSNotification([$item->user->device_token], $messages);
	            }
	            unset($item->user);
        	} catch (Exception $e) {
        		$messages = $e->getMessage();
                return response()->json([
                    'status' => false,
                    'messages'    => $messages,
                    'data'   => $validator->messages()->first()
                ], 400);
        	}
            return $this->response([
                    'status_code' => 200,
                    'messages'    => $messages,
                    'data'        => $item], 200);
            
        }
        return response()->json([
            'status' => false,
            'messages'   => $validator->messages()->first(),
            'data'   => []
        ], 200);
    }

    public function putAcceptBiddingItem($id, Request $request)
    {
        $rules = [
                'id'=>'required|regex:/^[0-9]+$/',
                'status'=>'required|in:0,1,2,3'
        ];
        $validator = Validator::make(array_merge($request->all(),['id'=>$id]), $rules);
        if ( $validator->passes() ) {
        	try {
	            $data = false;
	            $user = $this->user;
	        	if(empty($user))
	        		throw new Exception("Unauthorized", 400);	

	            $biding = Bids::whereHas('item', function ( $query ) use ($user) {
				                $query->where('user_id', $user->id );
				               })->find($id);


	        	if(empty($biding))
	        		throw new Exception("biding item id does not exist", 400);	            

	        	$item = Items::with(array('user'=>function($query){
	                        $query->leftJoin('user_tokens', 'users.id', '=', 'user_tokens.user_id')->select('users.id','user_tokens.device_token');
	                    }))->find($biding->item->id);
	        	// 1:waiting accepts / 2:accepted / 3:unaccepts
	            $biding->status = $request->status;
	            if ($biding->save()) {
	                //$notifySetting
	                $messages = ' biding item ' . $biding->item->title . ' $' . $biding->price_bidding . ' is accepted';

	                $this->message->pushBidingMessageToUser($user->id, $parameter = ['bidId' => $biding->id, 'content' => $messages]);	            }
        	} catch (Exception $e) {
        		$messages = $e->getMessage();
        	}
            return $this->response([
                    'status_code' => 200,
                    'messages'    => $messages,
                    'data'        => $data], 200);
        }
        return response()->json([
            'status' => false,
            'messages'   => $validator->messages()->first(),
            'data' => []
        ], 200);
    }

    public function putRemoveBiddingItem($id, Request $request)
    {
        $rules = [
                'id'=>'required|regex:/^[0-9]+$/'
        ];
        $validator = Validator::make(array_merge($request->all(),['id'=>$id]), $rules);
        if ( $validator->passes() ) {
        	try {
	            $data = false;
	            $user = $this->user;
	        	if(empty($user))
	        		throw new Exception("Unauthorized", 400);	

	            $biding = Bids::whereHas('item', function ( $query ) use ($user) {
				                $query->where('user_id', $user->id );
				               })->find($id);


	        	if(empty($biding))
	        		throw new Exception("biding item id does not exist", 400);	            

	        	$item = Items::with(array('user'=>function($query){
	                        $query->leftJoin('user_tokens', 'users.id', '=', 'user_tokens.user_id')->select('users.id','user_tokens.device_token');
	                    }))->find($biding->item->id);
	        	// 9 removes
	            $biding->status = 9;
	            if ($biding->save()) {
	                //$notifySetting
	                $messages = ' biding item ' . $biding->item->title . ' $' . $biding->price_bidding . ' is remove';

	                $this->message->pushBidingMessageToUser($user->id, $parameter = ['bidId' => $biding->id, 'content' => $messages]);
	                // sendiOSNotification([$item->user->device_token], $messages);
	            }
        	} catch (Exception $e) {
        		$messages = $e->getMessage();
        	}
            return $this->response([
                    'status_code' => 200,
                    'messages'    => $messages,
                    'data'        => $data], 200);
        }
        return response()->json([
            'status' => false,
            'messages'   => $validator->messages()->first(),
            'data' => []
        ], 200);
    }


}
