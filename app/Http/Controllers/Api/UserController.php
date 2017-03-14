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
use App\Models\Wishlists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class UserController extends BaseController
{
    public function __construct(Request $request) {
      parent::__construct($request);
    }

    public function postSignup(Request $request)
    {
        $rules = [
            'name'      => 'required',
            'gender'        => 'required|in:male,female',
            'curr_long'     =>'required',
            'curr_lat'      =>'required',
            'location'         =>'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ( $validator->passes() ) {
            $user = User::where('fb_id',$request->facebook_id)->first();
            if(empty($user))
                $user =  new User();
            
            $user->fb_id = $request->facebook_id;
            $user->name = $request->name;
            $user->email =  $request->email;
            $user->status =  1;
            $user->gender = ucfirst($request->gender);
            $user->dob = date('Y-m-d',strtotime($request->dob));
            $user->phone = isset($request->phone)?$request->phone:'N/A';
            $user->profile_image = $request->url;
            $user->curr_lat = $request->has('curr_lat')? $request->curr_lat : '';
            $user->curr_long = $request->has('curr_long')? $request->curr_long : '';
            $user->location = $request->location? $request->location : 'N/A';
            $user->save();

            $device_token = '';
            // get new token
            $token = $user->login($user->id);
            if(empty($token))
            {
                $messages['error'] = 'User has been deleted.';
                return response()->json([
                    'status' => false,
                    'data'   =>$messages
                ], 200);

            }
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
            $user->gender = ucfirst($request->gender);
            $user->dob = date('Y-m-d',strtotime($request->dob));
            $user->profile_image = $request->avatar;
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
            $rules = [
                'notify'      => 'required',
                'distance'        => 'required',
                'low_price'              =>'required',
                'high_price'              =>'required'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ( $validator->passes() ) {
                $setting = Setting::where('user_id',$user->id)->first();
                if($setting)
                {
                    $setting->notify = $request->notify;
                    $setting->distance = $request->distance;
                    $setting->low_price = $request->low_price;
                    $setting->high_price = $request->high_price;
                    $setting->save();
                }else
                {
                    $setting = new Setting();
                    $setting->user_id = $user->id;
                    $setting->notify = $request->notify;
                    $setting->distance = $request->distance;
                    $setting->low_price = $request->low_price;
                    $setting->high_price = $request->high_price;
                    $setting->save();
                }
                $user = $this->user;
                $user['setting'] = $setting;
                return response()->json([
                    'status' => true,
                    'data'   => $user
                ], 200); ;
            }
            foreach ($validator->messages()->toArray() as $key => $msg) {
                $messages[$key] = reset($msg);
            }

            return response()->json([
                'status' => false,
                'data'   => $messages
            ], 200);
        }

        $messages['error'] = 'User not found.';
            return response()->json([
                'status' => false,
                'data'   => $messages
            ], 200);
    }

    public function get_my_bids(Request $request)
    {
        if ($request->has('key') ) {
            $user = $this->user;
            if (!$user) {
                $messages['error'] = 'User token is invalid.';
                return response()->json([
                    'status' => true,
                    'data' => $messages
                ], 200);
            }
            $page  = $request->has('page')?$request->page:1;
            //$bids = Bids::with('item','user')->where('user_id',$user->id)->orderBy('created_at','DESC')->take(15)->get();
            $bids = Items::with('user','pictures','category','user')
                ->join('bids', 'bids.item_id', '=', 'items.id')
                ->select(array('items.*'))
                ->where('bids.user_id',$user->id)
                ->orderBy('bids.created_at', 'DESC')
                ->skip(($page-1)*10)
                ->take(10)
                ->get();
            $nextPage = Items::with('user','pictures','category','user')
                ->join('bids', 'bids.item_id', '=', 'items.id')
                ->select(array('items.*'))
                ->where('bids.user_id',$user->id)
                ->orderBy('bids.created_at', 'DESC')
                ->skip($page*10)
                ->take(10)
                ->get();
            return response()->json([
                'status' => true,
                'data'   => $bids,
                'next_page' => count($nextPage) ? true : false
            ], 200); ;
        }

        $messages['error'] = 'User not found.';
        return response()->json([
            'status' => false,
            'data'   => $messages
        ], 200); ;
    }

    public function delete_account(Request $request)
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
            $user->status = 0;
            $user->save();
            $token = $this->token;
            $token->delete();
            $messages['error'] = 'User has been delete.';
            return response()->json([
                'status' => true,
                'data'   => $messages
            ], 200);
        }

        $messages['error'] = 'User not found.';
        return response()->json([
            'status' => false,
            'data'   => $messages
        ], 200);
    }
} 