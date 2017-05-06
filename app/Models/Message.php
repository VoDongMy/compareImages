<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 02:17
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Config,DB,Exception;

class Message extends Model {

    protected $table = 'messages';

    public function user() 
    {
        return $this->belongsTo('App\Models\User');
    }

    public function groupChat() 
    {
        return $this->belongsTo('App\Models\GroupChat');
    }
    /**
    * 0 :chat, 1 :biding
    */
    public function sendMessageToUser($userId, $parameter = array('toUserId'=>0,'content'=>''))
    {
        if ($toUser = User::find($parameter['toUserId'])) {
            $myGroupChat = GroupChat::whereHas('userGroupChats', function ( $query ) use ($parameter) {
                    return $query->where('user_id', $parameter['toUserId'] );
                })->where('user_id', $userId )->where('object_type',0)->first();

            $toGroupChat = GroupChat::whereHas('userGroupChats', function ( $query ) use ($userId) {
                    return $query->where('user_id', $userId );
                })->where('user_id', $parameter['toUserId'] )->where('object_type',0)->first();

            $groupChat = empty($myGroupChat)? (empty($toGroupChat)? null : $toGroupChat) : $myGroupChat;

            if (empty($groupChat)) {
                $groupChat = new GroupChat;
                $groupChat->user_id = $userId;
                $groupChat->title = $toUser->name;
                $groupChat->object_type = 0;
                $groupChat->save();
                $userGroupChat = new UserGroupChat;
                $userGroupChat->group_chat_id = $groupChat->id;
                $userGroupChat->user_id = $parameter['toUserId'];
                $userGroupChat->save();
            }
            return $this->pushMessageToGroup($groupChat->id,['userId'=>$userId,'content'=>$parameter['content']]);
        }
        throw new Exception("sending user id does not exist", 400);
        return (object)[];
    } 

    public function getSelectMessageByBox($id)
    {
        $groupChat = GroupChat::find($id);
        if (!empty($groupChat)) {
            return Message::where('group_chat_id',$id)->with(['user'=>function($query){
                        return $query->select('users.id', 'users.name','users.profile_image','users.location'); 
                    }]);
        }
        throw new Exception("item id does not exist", 400);
        return (object)[];
    }

    public function pushExchangeMessageToUser($userId, $parameter = array('exchangeId'=>0,'content'=>''))
    {
        $exchange = Exchanges::find($parameter['exchangeId']);
        if (empty($exchange)) 
            throw new Exception("exchange id does not exist", 400);

        $item = Items::with(array('user'=>function($query){
                        $query->leftJoin('user_tokens', 'users.id', '=', 'user_tokens.user_id')->select('users.id', 'users.name','user_tokens.device_token');
                    }))->find($exchange->item_id);
 
        if ($item) {
            $myGroupChat = GroupChat::where('user_id', $userId )->where('object_type',2)->where('object_id',$exchange->id)->first();

            $toGroupChat = GroupChat::whereHas('userGroupChats', function ( $query ) use ($userId) {
                    return $query->where('user_id', $userId );
                })->where('object_id',$exchange->id)->where('object_type',2)->first();

            $groupChat = empty($myGroupChat)? (empty($toGroupChat)? null : $toGroupChat) : $myGroupChat;

            if (empty($groupChat)) {
                $groupChat = new GroupChat;
                $groupChat->user_id = $userId;
                $groupChat->title = $item->user->name;
                $groupChat->object_type = 2;
                $groupChat->object_id = $exchange->id;
                $groupChat->save();
                $userGroupChat = new UserGroupChat;
                $userGroupChat->group_chat_id = $groupChat->id;
                $userGroupChat->user_id = $item->user_id;
                $userGroupChat->save();
            }

            switch ($exchange->status) {
                case 3:
                    Message::where('group_chat_id',$groupChat->id)->delete();
                    UserGroupChat::where('group_chat_id',$groupChat->id)->delete();
                    $groupChat->delete();
                    break;
                
                default:
                    # code...
                    break;
            }

            // send notify
            if ($exchange->status == 2)
                sendiOSNotification([$item->user->device_token], $messages = $parameter['content']);

            return $this->pushMessageToGroup($groupChat->id,['userId'=>$userId,'content'=>$parameter['content'],'type'=>0]);
        }
        throw new Exception("item id does not exist", 400);
        return (object)[];
    }

    public function pushBidingMessageToUser($userId, $parameter = array('bidId'=>0,'content'=>''))
    {
        $biding = Bids::find($parameter['bidId']);
        if (empty($biding)) 
            throw new Exception("bid item id does not exist", 400);

        $item = Items::with(array('user'=>function($query){
                        $query->leftJoin('user_tokens', 'users.id', '=', 'user_tokens.user_id')->select('users.id', 'users.name','user_tokens.device_token');
                    }))->find($biding->item_id);
 
        if ($item) {
            $myGroupChat = GroupChat::where('user_id', $userId )->where('object_type',1)->where('object_id',$biding->id)->first();

            $toGroupChat = GroupChat::whereHas('userGroupChats', function ( $query ) use ($userId) {
                    return $query->where('user_id', $userId );
                })->where('object_id',$biding->id)->where('object_type',1)->first();

            $groupChat = empty($myGroupChat)? (empty($toGroupChat)? null : $toGroupChat) : $myGroupChat;

            if (empty($groupChat)) {
                $groupChat = new GroupChat;
                $groupChat->user_id = $userId;
                $groupChat->title = $item->user->name;
                $groupChat->object_type = 1;
                $groupChat->object_id = $biding->id;
                $groupChat->save();
                $userGroupChat = new UserGroupChat;
                $userGroupChat->group_chat_id = $groupChat->id;
                $userGroupChat->user_id = $item->user_id;
                $userGroupChat->save();
            }

            switch ($biding->status) {
                case 3:
                    Message::where('group_chat_id',$groupChat->id)->delete();
                    UserGroupChat::where('group_chat_id',$groupChat->id)->delete();
                    $groupChat->delete();
                    break;
                
                default:
                    # code...
                    break;
            }

            // send notify
            if ($biding->status == 2)
                sendiOSNotification([$item->user->device_token], $messages = $parameter['content']);

            return $this->pushMessageToGroup($groupChat->id,['userId'=>$userId,'content'=>$parameter['content'],'type'=>0]);
        }
        throw new Exception("item id does not exist", 400);
        return (object)[];
    }

    public function pushMessageToGroup($id, $parameter = array('userId'=>0,'content'=>'', 'type' => 0))
    {
        if ($groupChat = GroupChat::find($id)) {
            $messages = new Message;
            $messages->user_id = $parameter['userId'];
            $messages->content = $parameter['content'];
            $messages->type = $parameter['type'];
            $messages->group_chat_id = $id;
            $messages->save();
            return Message::with('groupChat')->find($messages->id);
        }
        throw new Exception("group chat id does not exist", 400);
        return (object)[];
    } 

} 