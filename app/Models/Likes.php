<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 09/12/2015
 * Time: 22:05
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Likes extends Model {

    protected $table = 'likeable';

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function items()
    {
        return $this->morphedByMany('App\Models\Items', 'taggable');
    }

} 