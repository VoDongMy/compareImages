<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class GroupChat extends Model {

    protected $table = 'group_chats';

    protected $fillable = ['is_read'];

    public function user() {
        return $this->belongsTo('App\User');
    }

	public function userGroupChats()
    {
        return $this->hasMany('App\Models\UserGroupChat', 'group_chat_id');
    }
} 