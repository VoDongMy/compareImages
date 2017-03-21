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
} 