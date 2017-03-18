<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 01:50
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Items extends Model {

    protected $table = 'items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','price'];

    public function user() {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function category() {
        return $this->belongsTo('App\Models\Category','cat_id');
    }

    public function pictures()
    {
        return $this->hasMany('App\Models\Pictures','item_id');
    }
} 