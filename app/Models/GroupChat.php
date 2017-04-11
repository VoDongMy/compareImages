<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class GroupChat extends Model {

    protected $table = 'group_chats';

    protected $appends = ['message', 'object'];

    public function user() {
        return $this->belongsTo('App\User');
    }

	public function userGroupChats()
    {
        return $this->hasMany('App\Models\UserGroupChat', 'group_chat_id');
    }

    public function getListGroupByUserId($userId) 
    {
        return GroupChat::select('id', 'title', 'descript', 'created_at', 'updated_at')->where(function ($query) use ($userId) {
                        $query->whereHas('userGroupChats', function ( $query ) use ($userId) {
                            return $query->where('user_id', $userId );
                        })->orWhere('user_id', $userId);
                    })->orderBy('created_at','DESC')->get();
    }

    public function getObjectAttribute()
    {
    	switch ($this->object_type) {
    		case 0:
    			$object = Items::select('id','title','price')->find(1);
    			return empty($object)? (object)[] : $object;
    			break;

    		case 1:
    			$object = Items::select('id','title','price')->find($this->object_id);
    			return empty($object)? (object)[] : $object;
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