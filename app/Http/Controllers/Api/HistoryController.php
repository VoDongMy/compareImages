<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 01/12/2015
 * Time: 01:32
 */

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


class HistoryController extends BaseController {

    public function __construct(Request $request) {
        parent::__construct($request);
    }

    public function getHistories($type)
    {
        $rules = [
            'type'      => 'required|in:item'
        ];
        $validator = Validator::make(['type' => $type], $rules);
        if ( $validator->passes() ) {
	        $user = $this->user;
	            if( empty($user))
	                return response()->json([
	                        'status_code' => 401,
	                        'messages'    => 'Unauthorized',
	                        'data'        => array()
	                        ],401);
	        $data = History::where('user_id',$user->id)->where('history_type',$type)->first();
	        return $this->response([
	                    'status_code' => 200,
	                    'messages'    => 'request success',
	                    'data'        => ['user' => $user, 'history_item' => $data->history]], 200);
	    }
	    return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function putHistories($type,Request $request)
    {
        $rules = [
            'type'      => 'required|in:item'
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
	        $history = History::where('user_id',$user->id)->where('history_type',$type)->first();
	        if (empty($history))
	        	$history = new History;
	        	$history->history_type = 'item';
	        	$history->history = json_encode($request->json()->all());
	        	$history->user_id = $user->id;
	        	$history->save();
	        return $this->response([
	                    'status_code' => 200,
	                    'messages'    => 'request success',
	                    'data'        => ['user' => $user, 'history_item' => $history->history]], 200);
	    }
	    return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }
}