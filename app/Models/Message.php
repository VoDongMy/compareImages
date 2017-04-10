<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 02:17
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Config,DB;

class Message extends Model {

    protected $table = 'messages';

    public function user() 
    {
        return $this->belongsTo('App\Models\User');
    }

    public function sendMessageToUser($userId, $parameter = array('toUserId'=>0,'content'=>''))
    {
        // var_dump($userId);die;
        if (User::find($parameter['toUserId'])) {
            $GroupChat = GroupChat::whereRaw( DB::raw(''))->where('type',0)->first();
            var_dump($groupChat);die;
            // if () {
            //     # code...
            // }
        }
        throw new Exception("sending user id does not exist", 400);
        return (object)[];
        
    } 
} 