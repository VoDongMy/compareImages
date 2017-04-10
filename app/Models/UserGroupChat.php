<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserGroupChat extends Model {

    protected $table = 'user_group_chats';

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function groupChat() {
        return $this->belongsTo('App\GroupChat');
    }

} 