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
use App\Models\Watch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use App\Helpers\DataLog;



class ItemController extends BaseController{

    private $items;
    private $history;

    public function __construct(Request $request, Items $items, History $history) {
        parent::__construct($request);
        $this->items = $items;
        $this->history = $history;
    }

    public function getlistMyItem(Request $request)
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
            $user = $this->user;
            if(empty($user))
                return response()->json([
                    'status_code' => 401,
                    'messages'    => 'Unauthorized',
                    'data'        => array()
                    ],401);            
            $page  = $request->has('page')?$request->page:1;
            $items = Items::with('pictures','category')
                ->leftJoin('users', 'users.id', '=', 'items.user_id')
                ->select(array('items.*'))
                ->where('users.id',$user->id);

            $total = $items->count();

            if ((int)$request->input('limit')<=0) {
                $limit = 0;
                $maxPage = $page = 1;
                $response = $items->orderBy('items.created_at', $orderBy)->get();
            } else {
                // paging data
                $maxPage = ceil($total / $limit);
                $skip = $limit*((int)$page-1);
                $response = $items->take((int)$limit)->skip($skip)->orderBy('items.created_at', $orderBy)->get();
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

    public function getlistItem(Request $request)
    {
        $rules = [
            'maximun-distance' => 'regex:/^[0-9]+$/',
            'quantity' => 'required|regex:/^[+-]?[0-9]+$/',
            'keyword' => '',
            'category' => 'regex:/^([0-9]+,?)+$/',
            'condition' => 'in:0,1',
            'order_by' => 'in:asc,desc',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $user = $this->user;
            if(empty($user))
                return response()->json([
                    'status_code' => 401,
                    'messages'    => 'Unauthorized',
                    'data'        => array()
                    ],401);            
            $historyItem = History::where('user_id',$user->id)->where('history_type','item')->first();
            $arrIsReadItemId = empty($historyItem->history->reading_item_id)? 0 : $historyItem->history->reading_item_id;
            $direction = $request->has('quantity') && $request->quantity != 0? $request->quantity : 15;
            $orderBy = $request->has('order_by')? $request->order_by : 'asc';
            $page  = $request->has('page')?$request->page:1;
            $items = Items::with('pictures','category','user')->where('user_id','!=',$user->id)
                    ->select(array('items.*'));
       
            if ($request->has('keyword')) 
                $items = $items->where(function ($query) use ($request){
                        $query->where('title', 'like', '%' . $request->keyword . '%')
                              ->orWhere('descript', 'like', '%' . $request->keyword . '%')
                              ->orWhere('price', 'like', '%' . $request->keyword . '%')
                              ->orWhere('created_at', 'like', '%' . $request->keyword . '%');
                    });

            if ($request->has('category') ) {
                $cat_id = explode(',', $request->category);
                if (!in_array('0', $cat_id))
                    $items = $items->whereIn('cat_id', $cat_id);
            }
            if ($request->has('min-price') && (int)$request->{'min-price'} > 0) 
                $items = $items->where('price','>=',$request->{'min-price'});

            if ($request->has('max-price') && (int)$request->{'max-price'} > 0) 
                $items = $items->where('price','<=',$request->{'max-price'});
            // echo json_encode($items->get());die;
            if (!empty($arrIsReadItemId) && is_array($arrIsReadItemId))
                $items = $items->whereNotIn('id',$arrIsReadItemId);
            $total = $items->count();
            if ($direction != 0) {
                $limit = abs($direction);
            } else {
                $limit = $total;
            }
            $maxPage = ceil($total / $limit);
            $tmpPage = 0;
            $responseData = array();
            if ($request->has('maximun-distance') && (int)$request->{'maximun-distance'} > 0) 
                while(count($responseData) <= $limit && $tmpPage <= $maxPage) {
                    $tmpPage += 1;
                    $skip = $limit*((int)$tmpPage-1);
                    $response = $items->take($limit)->skip($skip)->orderBy('items.created_at', $orderBy)->get();
                    foreach ($response as $key => $item) {
                        $localtionA = $user;
                        $localtionB = User::find($item->user_id);
                        if (empty($localtionA) || empty($localtionB) || empty($localtionA->curr_lat) || empty($localtionB->curr_lat) || empty($localtionA->curr_long) || empty($localtionB->curr_long)) {
                            return '0 km';
                        }
                        $distance =  getDistanceByLatLong($localtionA = ['lat'=>$localtionA->curr_lat,'long'=>$localtionA->curr_long],$localtionB = ['lat'=>$localtionB->curr_lat,'long'=>$localtionB->curr_long]);
                        $item->distance = $distance . ' km';
                        if ($distance <= (int)$request->{'maximun-distance'})
                            array_push($responseData, $item); 
                    }
                } 
            else {
                $skip = $limit*((int)$page-1);
                $responseData = $items->take((int)$limit)->skip($skip)->orderBy('items.created_at', $orderBy)->get();
            }

            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => (object)['total' => $total,
                                            'limit' => $limit,
                                            'page' => $page,
                                            // 'max_page' => $maxPage,
                                            'items' => $responseData]
                                            ], 200); 
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function postCreateItem(Request $request)
    {
        $rules = [
            'title'      => 'required',
            'descript'      => 'required',
            'cat_id'     =>'required|regex:/^[0-9]+$/',
            'price'     =>'required',
            'status'     =>'regex:/^[0-9]$/',
            'images.*'      =>'regex:/^([0-9]+,?)+$/',//, 'max:200px'
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
            $item = new Items();
            $item->title = $request['title'];
            $item->descript = $request['descript'];
            $item->price = $request['price'];
            $item->user_id  = $user->id;
            $item->cat_id  = $request['cat_id'];
            $item->status  = $request->has('status')? $request->status : 1;
            $item->save();
            if($request->has('images'))
            {
                $imageId = explode(",",$request->input('images'));
                foreach ($imageId as $key => $id) {
                    $picture = Pictures::find($id);
                    if (empty($picture))
                        continue;
                    $picture->item_id = $item->id;
                    $picture->save();
                }
            }
            $items = Items::with('pictures','category')->where('id',$item->id)->first();
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $items], 200); 
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function putUpdateItem($id, Request $request)
    {
        $rules = [
            'id'      => 'required|regex:/^[0-9]+$/',
            'title'      => 'required',
            'descript'      => 'required',
            'status'     =>'regex:/^[0-9]$/',
            'price'     =>'required',
            'images.*'      =>'',//, 'max:200px'
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
            $item = Items::find($id);
            if( empty($item))
                return response()->json([
                        'status_code' => 400,
                        'messages'    => 'Item not found.',
                        'data'        => array()
                        ],400); 
            $item->title = $request['title'];
            $item->descript = $request['descript'];
            $item->price = $request['price'];
            $item->user_id  = $user->id;
            $item->cat_id  = $request['cat_id'];
            $item->status  = $request->has('status')? $request->status : 1;
            $item->save();
            if($request->has('images'))
            {
                $imageId = explode(",",$request->input('images'));
                Pictures::whereNotIn('item_id',$imageId)->update(['item_id'=>0]);
                $listImages = Pictures::whereIn('item_id',$imageId)->lists()->toArray();
                foreach ($imageId as $key => $id) {
                    if (in_array($id, $listImages)) 
                        continue;
                    $picture = Pictures::find($id);
                    if ($picture) 
                        continue;
                    $picture->item_id = $item->id;
                    $picture->save();
                }
            } else {
                Pictures::where('item_id',$id)->update(['item_id'=>0]);
            }
            $items = Items::with('pictures','category')->where('id',$item->id)->first();
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $items], 200); 
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function deleteRemoveItem($id, Request $request)
    {
        $rules = [
            'id'      => 'required|regex:/^[0-9]+$/'
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
            $item = Items::where('user_id',$user->id)->find($id);
            if( empty($item))
                return response()->json([
                        'status_code' => 400,
                        'messages'    => 'Item not found.',
                        'data'        => array()
                        ],400); 
            $item->delete();
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => (object)['remove item'=>true]], 200); 
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function putUnWatchItem($id, Request $request)
    {
        $rules = [
                'id'     =>'regex:/^[0-9]+$/'
            ];
        $validator = Validator::make(['id'=>$id], $rules);
        if ($validator->passes()) {
            $user = $this->user;
            if( empty($user))
                return response()->json([
                        'status_code' => 401,
                        'messages'    => 'Unauthorized',
                        'data'        => array()
                        ],401); 
            $item = Items::find($id);
            if( empty($item))
                return response()->json([
                        'status_code' => 400,
                        'messages'    => 'Item not found.',
                        'data'        => array()
                        ],400);
            $watch = Watch::where('watch_id',$id)->where('watch_type','item')->where('user_id',$user->id)->first();
            if(!empty($watch))
            { 
                $watch->delete();
            } 
            $this->history->putHistoryReadItem($user->id, $item->id);
            
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $item], 200); 
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function putUnLikeItem($id, Request $request)
    {
        $rules = [
                'id'     =>'regex:/^[0-9]+$/'
            ];
        $validator = Validator::make(['id'=>$id], $rules);
        if ($validator->passes()) {
            $user = $this->user;
            if( empty($user))
                return response()->json([
                        'status_code' => 401,
                        'messages'    => 'Unauthorized',
                        'data'        => array()
                        ],401); 
            $item = Items::find($id);
            if( empty($item))
                return response()->json([
                        'status_code' => 400,
                        'messages'    => 'Item not found.',
                        'data'        => array()
                        ],400); 
            $like = Likes::where('like_id',$id)->where('like_type','item')->where('user_id',$user->id)->first();
            if(!empty($like))
            {
                $like->delete();    
            } 
            $this->history->putHistoryReadItem($user->id, $item->id);
            $item->dislike_count = (int)$item->dislike_count + 1;
            $item->save();
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $item], 200); 
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function putLikeItem($id, Request $request)
    {
        $rules = [
                'id'     =>'regex:/^[0-9]+$/'
            ];
        $validator = Validator::make(['id'=>$id], $rules);
        if ($validator->passes()) {
            $user = $this->user;
            if( empty($user))
                return response()->json([
                        'status_code' => 401,
                        'messages'    => 'Unauthorized',
                        'data'        => array()
                        ],401); 
            $item = Items::find($id);
            if( empty($item))
                return response()->json([
                        'status_code' => 400,
                        'messages'    => 'Item not found.',
                        'data'        => array()
                        ],400); 
            $this->history->putHistoryReadItem($user->id, $item->id);
            $like = Likes::where('like_id',$id)->where('like_type','item');
            if(empty($like->where('user_id',$user->id)->first()))
            {
                $like = new Likes;
                $like->user_id = $user->id;
                $like->like_id = $id;
                $like->like_type = 'item';
                $like->save();
                $item->like_count = count($like->lists('id')->toArray());
                $item->save();     
            }
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $item], 200); 
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function putWatchItem($id, Request $request)
    {
        $rules = [
                'id'     =>'regex:/^[0-9]+$/'
            ];
        $validator = Validator::make(['id'=>$id], $rules);
        if ($validator->passes()) {
            $user = $this->user;
            if( empty($user))
                return response()->json([
                        'status_code' => 401,
                        'messages'    => 'Unauthorized',
                        'data'        => array()
                        ],401); 
            $item = Items::find($id);
            if( empty($item))
                return response()->json([
                        'status_code' => 400,
                        'messages'    => 'Item not found.',
                        'data'        => array()
                        ],400); 
            $this->history->putHistoryReadItem($user->id, $item->id);
            $watch = Watch::where('watch_id',$id)->where('watch_type','item');
            if(empty($watch->where('user_id',$user->id)->first()))
            {
                $watch = new Watch;
                $watch->user_id = $user->id;
                $watch->watch_id = $id;
                $watch->watch_type = 'item';
                $watch->save(); 
            }
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $item], 200); 
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function getLikelistItems(Request $request)
    {
        // $rules = [
        //         'id'     =>'regex:/^[0-9]+$/'
        //     ];
        // $validator = Validator::make(['id'=>$id], $rules);
        // if ($validator->passes()) {
            $user = $this->user;
            if( empty($user))
                return response()->json([
                        'status_code' => 401,
                        'messages'    => 'Unauthorized',
                        'data'        => array()
                        ],401); 
            $items = Items::select('items.*')->with('pictures','category','user')->join('likeable', function ($join) use ($user) {
                        $join->on('likeable.like_id', '=', 'items.id')
                             ->where('likeable.user_id', '=', $user->id)
                             ->where('likeable.like_type', '=', 'item');
                    })->get();
            if( empty($items))
                return response()->json([
                        'status_code' => 400,
                        'messages'    => 'Item not found.',
                        'data'        => array()
                        ],400); 
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $items], 200); 
        // }
        // return $this->response([
        //             'status_code' => 400,
        //             'messages'    => $validator->messages()->first(),
        //             'data'        => array()
        //             ], 400);
    }

    public function getWatchlistItems(Request $request)
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
            $user = $this->user;
            if(empty($user))
                return response()->json([
                    'status_code' => 401,
                    'messages'    => 'Unauthorized',
                    'data'        => array()
                    ],401);            
            $page  = $request->has('page')?$request->page:1;
            $items = Items::select('items.*')->with('pictures','category','user')->join('watchable', function ($join) use ($user) {
                        $join->on('watchable.watch_id', '=', 'items.id')
                             ->where('watchable.user_id', '=', $user->id)
                             ->where('watchable.watch_type', '=', 'item');
                    });

            $total = $items->count();

            if ((int)$request->input('limit')<=0) {
                $limit = 0;
                $maxPage = $page = 1;
                $response = $items->orderBy('items.created_at', $orderBy)->get();
            } else {
                // paging data
                $maxPage = ceil($total / $limit);
                $skip = $limit*((int)$page-1);
                $response = $items->take((int)$limit)->skip($skip)->orderBy('items.created_at', $orderBy)->get();
            }
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => (object)['total' => $total,
                                            'limit' => $limit,
                                            'page' => $page,
                                            'max_page' => $maxPage,
                                            'items' => $response]
                                            ], 200); 
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    public function getItemDetail($id)
    {
        $rules = [
                'id'     =>'regex:/^[0-9]+$/'
            ];
        $validator = Validator::make(['id'=>$id], $rules);
        if ($validator->passes()) {
            $user = $this->user;
            if( empty($user))
                return response()->json([
                        'status_code' => 401,
                        'messages'    => 'Unauthorized',
                        'data'        => array()
                        ],401); 
            $item = Items::with('pictures','category','user')->find($id);
            if(ßempty($item))
                return response()->json([
                        'status_code' => 400,
                        'messages'    => 'Item not found.',
                        'data'        => array()
                        ],400); 
            $this->history->putHistoryReadItem($user->id, $item->id);
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $item], 200); 
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);
    }

    //
    public function show(Request $request)
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

            $select = "3959 * acos( cos( radians(".$user->curr_lat.") ) * cos( radians( users.curr_lat ) ) * cos( radians( users.curr_long ) - radians( ".$user->curr_long." ) ) + sin( radians( ".$user->curr_lat." ) ) * sin( radians( users.curr_lat ) ) )";

            $items = Items::with('user', 'pictures','category')
                ->leftJoin('users', 'users.id', '=', 'items.user_id')
                ->select(array('items.*',DB::Raw('('.$select.') as distance')))
                ->where('users.id','<>',$user->id)
                ->orderBy('distance', 'ASC')
                ->skip(($page-1)*10)
                ->take(10)
                ->get();
            $nextPage = Items::with('user', 'pictures','category')
                ->leftJoin('users', 'users.id', '=', 'items.user_id')
                ->select(array('items.*',DB::Raw('('.$select.') as distance')))
                ->where('users.id','<>',$user->id)
                ->orderBy('distance', 'ASC')
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

    public function finding(Request $request)
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
            $category = $request->category;
            $type = $request->filler;
            $page  = $request->has('page')?$request->page:1;
            switch($type){
                case 'nearest':
                    $select = "3959 * acos( cos( radians(".$user->curr_lat.") ) * cos( radians( users.curr_lat ) ) * cos( radians( users.curr_long ) - radians( ".$user->curr_long." ) ) + sin( radians( ".$user->curr_lat." ) ) * sin( radians( users.curr_lat ) ) )";

                    if($category)
                    {
                        $items = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->leftJoin('categories', 'categories.id', '=', 'items.cat_id')
                            ->select(array('items.*',DB::Raw('('.$select.') as distance')))
                            ->where('users.id','<>',$user->id)
                            ->where('categories.id',$category)
                            ->orderBy('distance', 'ASC')
                            ->skip(($page-1)*10)
                            ->take(10)
                            ->get();
                        $nextPage = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->leftJoin('categories', 'categories.id', '=', 'items.cat_id')
                            ->select(array('items.*',DB::Raw('('.$select.') as distance')))
                            ->where('users.id','<>',$user->id)
                            ->where('categories.id',$category)
                            ->orderBy('distance', 'ASC')
                            ->skip($page*10)
                            ->take(10)
                            ->get();
                    }else{
                        $items = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->select(array('items.*',DB::Raw('('.$select.') as distance')))
                            ->where('users.id','<>',$user->id)
                            ->orderBy('distance', 'ASC')
                            ->skip(($page-1)*10)
                            ->take(10)
                            ->get();
                        $nextPage = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->select(array('items.*',DB::Raw('('.$select.') as distance')))
                            ->where('users.id','<>',$user->id)
                            ->orderBy('distance', 'ASC')
                            ->skip($page*10)
                            ->take(10)
                            ->get();
                    }

                    break;
                case 'lowest':
                    if($category)
                    {
                        $items = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->leftJoin('categories', 'categories.id', '=', 'items.cat_id')
                            ->select(array('items.*'))
                            ->where('categories.id',$category)
                            ->where('users.id','<>',$user->id)
                            ->orderBy('price', 'ASC')
                            ->skip(($page-1)*10)
                            ->take(10)
                            ->get();
                        $nextPage = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->leftJoin('categories', 'categories.id', '=', 'items.cat_id')
                            ->select(array('items.*'))
                            ->where('categories.id',$category)
                            ->where('users.id','<>',$user->id)
                            ->orderBy('price', 'ASC')
                            ->skip($page*10)
                            ->take(10)
                            ->get();
                    }else{
                        $items = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->where('users.id','<>',$user->id)
                            ->select(array('items.*'))
                            ->orderBy('price', 'ASC')
                            ->skip(($page-1)*10)
                            ->take(10)
                            ->get();
                        $nextPage = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->where('users.id','<>',$user->id)
                            ->select(array('items.*'))
                            ->orderBy('price', 'ASC')
                            ->skip($page*10)
                            ->take(10)
                            ->get();
                    }
                    break;
                case 'highest':
                    if($category)
                    {
                        $items = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->leftJoin('categories', 'categories.id', '=', 'items.cat_id')
                            ->select(array('items.*'))
                            ->where('users.id','<>',$user->id)
                            ->where('categories.id',$category)
                            ->orderBy('price', 'DESC')
                            ->skip(($page-1)*10)
                            ->take(10)
                            ->get();
                        $nextPage = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->leftJoin('categories', 'categories.id', '=', 'items.cat_id')
                            ->select(array('items.*'))
                            ->where('users.id','<>',$user->id)
                            ->where('categories.id',$category)
                            ->orderBy('price', 'DESC')
                            ->skip($page*10)
                            ->take(10)
                            ->get();
                    }else{
                        $items = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->where('users.id','<>',$user->id)
                            ->select(array('items.*'))
                            ->orderBy('price', 'DESC')
                            ->skip(($page-1)*10)
                            ->take(10)
                            ->get();
                        $nextPage = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->where('users.id','<>',$user->id)
                            ->select(array('items.*'))
                            ->orderBy('price', 'DESC')
                            ->skip($page*10)
                            ->take(10)
                            ->get();
                    }
                    break;
                default:
                    if($category)
                    {
                        $items = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->leftJoin('categories', 'categories.id', '=', 'items.cat_id')
                            ->where('users.id','<>',$user->id)
                            ->select(array('items.*'))
                            ->where('categories.id',$category)
                            ->orderBy('created_at', 'DESC')
                            ->skip(($page-1)*10)
                            ->take(10)
                            ->get();
                        $nextPage = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->leftJoin('categories', 'categories.id', '=', 'items.cat_id')
                            ->where('users.id','<>',$user->id)
                            ->select(array('items.*'))
                            ->where('categories.id',$category)
                            ->orderBy('created_at', 'DESC')
                            ->skip($page*10)
                            ->take(10)
                            ->get();
                    }else{
                        $items = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->where('users.id','<>',$user->id)
                            ->select(array('items.*'))
                            ->orderBy('created_at', 'DESC')
                            ->skip(($page-1)*10)
                            ->take(10)
                            ->get();
                        $nextPage = Items::with('user', 'pictures','category')
                            ->leftJoin('users', 'users.id', '=', 'items.user_id')
                            ->where('users.id','<>',$user->id)
                            ->select(array('items.*'))
                            ->orderBy('created_at', 'DESC')
                            ->skip($page*10)
                            ->take(10)
                            ->get();
                    }
                    break;
            }

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

    public function create(Request $request)
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
                'title'      => 'required',
                'descript'      => 'required',
                'price'     =>'required',
                'image_1'      =>array('required', 'mimes:jpeg,jpg,png'),//, 'max:200px'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ( $validator->passes() ) {

                $item = new Items();
                $item->title = $request['title'];
                $item->descript = $request['descript'];
                $item->price = $request['price'];
                $item->user_id  = $user->id;
                $item->cat_id  = $request['cat_id'];
                $item->save();
                $destinationPath = env('ITEM_UPLOAD_PATH');
                for($i=1;$i<=6;$i++)
                {
                    if($request->hasFile('image_'.$i))
                    {
                        $picture = new Pictures();
                        $fileName =  date('YmdHis') . "-" . rand(100000, 999999) . "-" . rand(100000, 999999) . '.' .
                            $request->file('image_'.$i)->getClientOriginalExtension();
                        $request->file('image_'.$i)->move($destinationPath, $fileName);
                        $image = $destinationPath . $fileName;
                        $img = Image::make($image)->orientate();
                        $img->resize(600, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $img->save($image);
                        $picture->url = "http://".$_SERVER['HTTP_HOST'].'/'.$image;
                        $picture->item_id = $item->id;
                        $picture->save();

                    }
                }
                $items = Items::with('user', 'pictures','category')->where('id',$item->id)->first();
                return response()->json([
                    'status' => true,
                    'data'   =>$items
                ], 200); ;
            }
            foreach ($validator->messages()->toArray() as $key => $msg) {
                $messages[$key] = reset($msg);
            }

            return response()->json([
                'status' => false,
                'data'   => $messages
            ], 200);

        }else{
            $messages['error'] = 'User not found.';
            return response()->json([
                'status' => false,
                'data'   => $messages
            ], 200); ;
        }
    }

    public function remove_item(Request $request)
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

            $item = Items::find($request->item_id);
            $item->delete();
            $items = Items::with('user','pictures','category')
                ->leftJoin('users', 'users.id', '=', 'items.user_id')
                ->select(array('items.*'))
                ->where('users.id',$user->id)
                ->orderBy('items.created_at', 'DESC')
                ->take(10)
                ->get();
            return response()->json([
                'status' => true,
                'data'   =>$items
            ], 200);

        }else{
            $messages['error'] = 'User not found.';
            return response()->json([
                'status' => false,
                'data'   => $messages
            ], 200); ;
        }
    }

    public function bidding_item(Request $request)
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
            $rules = [
                'price' => 'required',
                'item_id'=>'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ( $validator->passes() ) {
                $bid = Bids::where('user_id',$user->id)->where('item_id',$request->item_id)->first();
                if($bid)
                {
                    $bid->item_id = $request->item_id;
                    $bid->price_bidding = $request->price;
                    $bid->status = 0;
                    $bid->save();

                }else{
                    $bid = new Bids();
                    $bid->user_id = $user->id;
                    $bid->item_id = $request->item_id;
                    $bid->price_bidding = $request->price;
                    $bid->status = 0;
                    $bid->save();
                }

                $messages['error'] = 'Your bidding has been sent.';
                return response()->json([
                    'status' => true,
                    'data'   => $messages
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
        ], 200); ;
    }

    public function get_bids(Request $request)
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
            $rules = [
                'item_id'=>'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ( $validator->passes() ) {
                $page  = $request->has('page')?$request->page:1;
                $bids = Bids::with('item','user')
                    ->where('item_id',$request->item_id)
                    ->orderBy('created_at','DESC')
                    ->skip(($page-1)*10)
                    ->take(10)
                    ->get();
                $nextPage =Bids::with('item','user')
                    ->where('item_id',$request->item_id)
                    ->orderBy('created_at','DESC')
                    ->skip($page*10)
                    ->take(10)
                    ->get();
                return response()->json([
                    'status' => true,
                    'data'   => $bids,
                    'next_page' => count($nextPage) ? true : false
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
        ], 200); ;
    }

    public function like_item(Request $request)
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
            $like = Likes::where('item_id',$request->item_id)->where('user_id',$user->id)->first();
            $dislike = 0;
            if($like)
            {
                $like->likes =$like->likes+1;
                if($like->dislike)
                {
                    $dislike = 1;
                }
                $like->dislike = $like->dislike?$like->dislike-1:$like->dislike;
                $like->save();
            }else{
                $like = new Likes();
                $like->user_id = $user->id;
                $like->item_id = $request->item_id;
                $like->likes =$like->likes+1;
                $like->save();
            }
            $item = Items::find($request->item_id);
            $item->like_count = $item->like_count+1;
            $item->dislike_count = ($item->dislike_count>0)?$item->dislike_count-$dislike:0;
            $item->save();
            $data =  Items::with('user', 'pictures','category')
                ->leftJoin('users', 'users.id', '=', 'items.user_id')
                ->select(array('items.*'))
                ->where('users.id','<>',$user->id)
                //->orderBy('distance', 'ASC')
                //->take(15)
                ->get();
            return response()->json([
                'status' => true,
                'data'   => $data
            ], 200); ;
        }

        $messages['error'] = 'User not found.';
        return response()->json([
            'status' => false,
            'data'   => $messages
        ], 200);
    }

    public function dislike_item(Request $request)
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

            $like = Likes::where('item_id',$request->item_id)->where('user_id',$user->id)->first();
            $like_count = 0;
            if($like)
            {
                $like->dislike =$like->dislike+1;
                if($like->likes)
                {
                    $like_count = 1;
                }
                $like->likes = $like->likes?$like->likes-1:$like->likes;
                $like->save();
            }else{
                $like = new Likes();
                $like->dislike =$like->dislike+1;
                $like->save();
            }
            $item = Items::find($request->item_id);
            $item->dislike_count= $item->dislike_count+1;
            $item->like_count  = ($item->like_count>0)?$item->like_count-$like_count:0;
            $item->save();
            $data =  Items::with('user', 'pictures','category')
                ->leftJoin('users', 'users.id', '=', 'items.user_id')
                ->select(array('items.*'))
                ->where('users.id','<>',$user->id)
                //->take(15)
                ->get();
            return response()->json([
                'status' => true,
                'data'   => $data
            ], 200); ;
        }

        $messages['error'] = 'User not found.';
        return response()->json([
            'status' => false,
            'data'   => $messages
        ], 200);
    }

    public function update_status_bid(Request $request)
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
            $bid = Bids::find($request->bid_id);
            $bid->status = $request->status;
            $bid->save();
            /**
             * Todo:
             * status = 1: match can chat with bidder
             * send notify to bidder
             * status = 2: Ignore
             */
            $messages['error'] = 'Your bidding has been sent.';
            return response()->json([
                'status' => true,
                'data'   => $messages
            ], 200);
        }

        $messages['error'] = 'User not found.';
        return response()->json([
            'status' => false,
            'data'   => $messages
        ], 200); ;
    }
} 
