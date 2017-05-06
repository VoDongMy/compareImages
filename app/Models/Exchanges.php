<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 01:52
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Exchanges extends Model{

    protected $table = 'exchanges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function item() {
        return $this->belongsTo('App\Models\Items');
    }


}