<?php

namespace App\Http\Controllers\Api;

/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 02:41
 */

use App\Models\Bids;
use App\Models\Items;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserToken;
use App\Models\Wishlists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use App\Helpers\DataLog;

use Hash;

class UserController extends BaseController
{
    protected $userModel;

    public function __construct(Request $request, User $userModel) {
      parent::__construct($request);
      $this->userModel = $userModel;
    }

    public function postSignup(Request $request)
    {
        $rules = [
            'email'         =>'required|email',
            'password'      => 'required|min:5',
        ];
	DataLog::logPublic('request.log', $request->url());
        DataLog::logPublic('request.log', $request->all());
    $validator = Validator::make($request->all(), $rules);
	if ( $validator->passes() ) {
            $user = User::where('email',$request->email)->first();
            if(empty($user)) {
                $user =  new User();
                $user->password = Hash::make($request->password);
                $user->email =  $request->email;
                $user->status =  1;
                $user->save();
            } elseif (!Hash::check($request->password, $user->password) ) {
                return $this->response([
                    'status_code' => 400,
                    'messages'    =>  'invalid email or password.',
                    'data'        => array()
                    ], 401);
            }

            // get new token
            $token = $this->userModel->login($user->id, $parameter = array('udid'=>'1234567890', 'device_type'=>'0'));
            if(empty($token))
            {
                return $this->response([
                    'status_code' => 400,
                    'messages'    =>  'User has been deleted.',
                    'data'        => array()
                    ], 401);

            }
	    $token = UserToken::with('user')->find($token->id);
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $token
                    ], 200);
        }
            
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 401);
    }

    public function putUpdate(Request $request)
    {
        
        $rules = [
            'name'      => 'required',
            'gender'        => 'required|in:male,female',
            'dob'              =>'date',
            'phone'         =>'required',
            'location'         =>'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $user = User::find($this->user->id);
            if (empty($user))
                return response()->json([
                    'status_code' => 401,
                    'messages'    => 'Unauthorized',
                    'data'        => array()
                    ],401);

            $user->name = $request->name;
            $user->gender = $request->gender;
            $user->dob = date('Y-m-d',strtotime($request->dob));
            $user->profile_image = $request->profile_image;
            $user->phone = $request->phone;
            $user->location = $request->location;
            $user->save();

            //$user->login($request->key);
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $user
                    ], 200);

        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function deleteAccount($id, Request $request)
    {
        $rules = [
            'id'      => 'required'
        ];

        $validator = Validator::make(['id' => $id], $rules);
        if ($validator->passes()) {
            $user = User::find($this->user->id);
            if (empty($user))
                return response()->json([
                    'status_code' => 401,
                    'messages'    => 'Unauthorized',
                    'data'        => array()
                    ],401);

            $user->status = 0;
            $user->save();
            $token = $this->token;
            $token->delete();
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $user
                    ], 200);        
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function getSignout(Request $request)
    {
        
        $user = User::find($this->user->id);
        if (empty($user))
            return response()->json([
                'status_code' => 401,
                'messages'    => 'Unauthorized',
                'data'        => array()
                ],401);

        $user->logout($user->id);
        return $this->response([
                'status_code' => 200,
                'messages'    => 'request success',
                'data'        => (object)[]
                ], 200);        
        
    }

    public function getMyBids(Request $request)
    {
        $rules = [
            'limit' => 'regex:/^[0-9]+$/',
            'page' => 'regex:/^[0-9]+$/',
            'order_by' => 'in:asc,desc',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $limit = $request->has('limit')? $request->limit : 0;
            $page = $request->has('page')? $request->page : 1;
            $orderBy = $request->has('order_by')? $request->order_by : 'asc';
            $user = User::find($this->user->id);    
            if (empty($user))
                return response()->json([
                    'status_code' => 401,
                    'messages'    => 'Unauthorized',
                    'data'        => array()
                    ],401);

            $bids = Items::with('pictures','category')
                ->join('bids', 'bids.item_id', '=', 'items.id')
                ->where('bids.user_id',$user->id);

            // paging data
            $total = $bids->count();

            if ((int)$request->input('limit')<=0) {
                $limit = 0;
                $maxPage = $page = 1;
                $response = $bids->orderBy('bids.created_at', $orderBy)->get();
            } else {
                $maxPage = ceil($total / $limit);
                $skip = $limit*((int)$page-1);
                $response = $bids->take((int)$limit)->skip($skip)->orderBy('bids.created_at', $orderBy)->get();
            }
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => (object)['total' => $total,
                                            'limit' => $limit,
                                            'page' => $page,
                                            'max_page' => $maxPage,
                                            'user' => $user,
                                            'items' => $response]
                                            ], 200);     
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);   
    }

    public function add_wishlist(Request $request)
    {
        if ($request->has('key') )
        {
            $user = $this->user;
            if(!$user)
            {
                $messages['error'] = 'User token is invalid.';
                return response()->json([
                    'status' => false,
                    'data'   => $messages
                ], 200);
            }
            $watchlist = Wishlists::where('user_id',$user->id)->where('item_id',$request->item_id)->first();
            if(!$watchlist)
            {

                $wishlist = new Wishlists();
                $wishlist->user_id = $user->id;
                $wishlist->item_id = $request->item_id;
                $wishlist->save();
            }

            $wishlist =Items::with('user', 'pictures','category')
                ->leftJoin('wishlists', 'items.id', '=', 'wishlists.item_id')
                ->select(array('items.*'))
                ->where('wishlists.user_id',$user->id)
                ->orderBy('wishlists.created_at','DESC')
                ->take(10)
                ->get();
            if($wishlist)
            {
                $data = $wishlist;
            }else{
                $data = array();

            }
            return response()->json([
                'status' => true,
                'data'   => $data
            ], 200);
        }else{
            $messages['error'] = 'Key token is required.';
            return response()->json([
                'status' => false,
                'data'   => $messages
            ], 200);
        }
    }

    public function remove_wishlist(Request $request)
    {
        if ($request->has('key') )
        {
            $user = $this->user;
            if(!$user)
            {
                $messages['error'] = 'User token is invalid.';
                return response()->json([
                    'status' => false,
                    'data'   => $messages
                ], 200);
            }

            $wishlist = Wishlists::where('user_id',$user->id)->where('item_id',$request->item_id)->first() ;
            $wishlist->delete();

            $wishlist =Items::with('user', 'pictures','category')
                ->leftJoin('wishlists', 'items.id', '=', 'wishlists.item_id')
                ->select(array('items.*'))
                ->where('wishlists.user_id',$user->id)
                ->orderBy('wishlists.created_at','DESC')
                ->take(10)
                ->get();
            if($wishlist)
            {
                $data = $wishlist;
            }else{
                $data = array();

            }
            return response()->json([
                'status' => true,
                'data'   => $data
            ], 200);
        }else{
            $messages['error'] = 'Key token is required.';
            return response()->json([
                'status' => false,
                'data'   => $messages
            ], 200);
        }
    }

    public function wishlist(Request $request)
    {
        if ($request->has('key') )
        {
            $user = $this->user;
            if(!$user)
            {
                $messages['error'] = 'User token is invalid.';
                return response()->json([
                    'status' => false,
                    'data'   => $messages
                ], 200);
            }
            $wishlist =Items::with('user', 'pictures','category')
                ->leftJoin('wishlists', 'items.id', '=', 'wishlists.item_id')
                ->select(array('items.*'))
                ->where('wishlists.user_id',$user->id)
                ->orderBy('wishlists.created_at','DESC')
                ->take(10)
                ->get();
            if($wishlist)
            {
                $data = $wishlist;
            }else{
                $data = array();

            }
            return response()->json([
                'status' => true,
                'data'   => $data
            ], 200);

        }else{
            $messages['error'] = 'Key token is required.';
            return response()->json([
                'status' => false,
                'data'   => $messages
            ], 200);
        }
    }

    public function listing(Request $request)
    {
        if ($request->has('key') )
        {
            $user = $this->user;
            if(!$user)
            {
                $messages['error'] = 'User token is invalid.';
                return response()->json([
                    'status' => false,
                    'data'   => $messages
                ], 200);
            }
            $page  = $request->has('page')?$request->page:1;
            $items = Items::with('user','pictures','category')
                ->leftJoin('users', 'users.id', '=', 'items.user_id')
                ->select(array('items.*'))
                ->where('users.id',$user->id)
                ->orderBy('items.created_at', 'DESC')
                ->skip(($page-1)*10)
                ->take(10)
                ->get();
            $nextPage =Items::with('user','pictures','category')
                ->leftJoin('users', 'users.id', '=', 'items.user_id')
                ->select(array('items.*'))
                ->where('users.id',$user->id)
                ->orderBy('items.created_at', 'DESC')
                ->skip($page*10)
                ->take(10)
                ->get();
            return response()->json([
                'status' => true,
                'data'   => $items,
                'next_page' => count($nextPage) ? true : false
            ], 200); ;
        }else{
            $messages['error'] = 'User not found.';
            return response()->json([
                'status' => false,
                'data'   => $messages
            ], 200); ;
        }
    }

    public function setting(Request $request)
    {

        $rules = [
            'notify_new_exchange'      => 'required|in:0,1',
            'notify_new_bids'      => 'required|in:0,1',
            'notify_messages'      => 'required|in:0,1',
            'distance'    => 'required',
            'low_price'   => 'required',
            'high_price'  => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ( $validator->passes() ) {
            $user = $this->user;
            if (empty($user))
                return response()->json([
                    'status_code' => 401,
                    'messages'    => 'Unauthorized',
                    'data'        => array()
                    ],401);
            $newSettings = $request->all();
            $setting = Setting::where('user_id',$user->id)->first();
            if (empty($setting))
                $setting = new Setting();
            $setting->user_id = $user->id;
            $setting->settings = json_encode($newSettings);
            $setting->save();
            
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $setting
                    ], 200);
        }
        return $this->response([
                'status_code' => 400,
                'messages'    => $validator->messages()->first(),
                'data'        => array()
                ], 400);    
    }

    public function getSetting(Request $request)
    {

        $rules = [
            // 'notify'      => 'required|in:0,1',
            // 'distance'    => 'required',
            // 'low_price'   => 'required',
            // 'high_price'  => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ( $validator->passes() ) {
            $user = $this->user;
            if (empty($user))
                return response()->json([
                    'status_code' => 401,
                    'messages'    => 'Unauthorized',
                    'data'        => array()
                    ],401);
            $setting = Setting::where('user_id',$user->id)->first();
            
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $setting
                    ], 200);
        }
        return $this->response([
                'status_code' => 400,
                'messages'    => $validator->messages()->first(),
                'data'        => array()
                ], 400);    
    }


} 
