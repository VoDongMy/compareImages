<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class GroupChat extends Model 
{

    protected $table = 'group_chats';

    protected $appends = ['message', 'object', 'list_users'];

    protected $fillable = ['id', 'object_id', 'object_type' ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function messages() {
        return $this->hasMany('App\Models\Message', 'group_chat_id');
    }

	public function userGroupChats()
    {
        return $this->hasMany('App\Models\UserGroupChat', 'group_chat_id');
    }

    public function getListUsersAttribute() 
    {

        return User::select('id','name','profile_image','location')->whereHas('userGroupChats', function ( $query ) {
                            return $query->where('group_chat_id', $this->id );
                        })->get();
    }

    public function getListGroupByUserId($userId) 
    {
        return GroupChat::select('id', 'title', 'descript', 'object_type', 'object_id', 'created_at', 'updated_at')->where(function ($query) use ($userId) {
                        $query->whereHas('userGroupChats', function ( $query ) use ($userId) {
                            return $query->where('user_id', $userId );
                        })->orWhere('user_id', $userId);
                    })->orderBy('created_at','DESC')->get();
    }

    public function getObjectAttribute()
    {
    	switch ($this->object_type) {
    		case 0:
    			return (object)[];
    			break;

    		case 1:
    			$object = Bids::select('bids.id as bid_id','items.id','items.title','items.user_id as item_user_id', 'bids.price_bidding as bidding_price', 'items.price as item_price', 'bids.status as status')->join('items', 'items.id', '=', 'bids.item_id')->find($this->object_id);
    			return empty($object)? (object)[] : array_merge($object->toArray(),['type' => 'biding']);
    			break;
    		
    		default:
    			return (object)[];
    			break;
    	}
        return (object)[];
    }

    public function getMessageAttribute()
    {
    	$message = Message::where('group_chat_id',$this->id)->orderBy('created_at','DESC')->first();
    	return empty($message)? '' : $message->content;
    }
} 