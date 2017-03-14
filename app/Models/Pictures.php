<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 02:22
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Pictures extends Model {

    protected $table = 'pictures';

    protected $fillable = ['item_id','url'];


    public function item() {
        return $this->belongsTo('App\Items');
    }


} 