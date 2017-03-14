<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 01:54
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Favorites extends Model {

    protected $table = 'favorites';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function item() {
        return $this->belongsTo('App\Items');
    }

} 