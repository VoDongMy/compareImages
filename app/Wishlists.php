<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 02:31
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Wishlists extends Model {


    protected $table = 'wishlists';

    protected $fillable = ['item_id','user_id'];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function item() {
        return $this->belongsTo('App\Items');
    }

} 