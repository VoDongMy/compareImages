<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 09/12/2015
 * Time: 22:05
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class History extends Model {

    protected $table = 'histories';

    protected $fillable = [
        'id', 'user_id', 'history_type', 'history'
    ];


    public function user() {
        return $this->belongsTo('App\User');
    }

    public function getHistoryAttribute($value)
    {
        return empty($value)? (object)[] : json_decode($value) ;
    }

    public function putHistories($userId, $value = array(), $historyType)
    {
        $history = History::where('user_id',$userId)->where('history_type','item')->first();
        if (empty($history)){
            $history = new History;
            $history->history = json_encode((object)$value);
        } else {
            $dataObject = $history->history;
            $history->history = json_encode(array_merge((array)$dataObject, $value));
        }
        $history->history_type = $historyType;
        $history->user_id = $userId;
        return $history->save();
    }

    public function putHistoryReadItem($userId, $itemId)
    {
        $historyItem = History::where('user_id',$userId)->where('history_type','item')->first();
        $readingItemId = isset($historyItem->history->reading_item_id)? 
                            (is_array($historyItem->history->reading_item_id)? 
                                (in_array($itemId, $historyItem->history->reading_item_id)? $historyItem->history->reading_item_id : array_merge($historyItem->history->reading_item_id, [$itemId])) : [$itemId]) 
                            : [$itemId];
        $this->putHistories($userId,["reading_item_id" => $readingItemId], $historyType = 'item');
    }

} 
