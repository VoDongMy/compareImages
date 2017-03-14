<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 02:23
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Notifications extends Model {

    protected $table = 'notifications';

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function item() {
        return $this->belongsTo('App\Items');
    }
} 