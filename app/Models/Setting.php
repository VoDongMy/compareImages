<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 02:31
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Setting extends Model{

    protected $table = 'settings';

    public function user() {
        return $this->belongsTo('App\User');
    }
} 