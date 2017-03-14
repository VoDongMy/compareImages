<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 02:17
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Message extends Model {

    protected $table = 'messages';

    protected $fillable = ['is_read'];

    public function from_user() {
        return $this->belongsTo('App\User');
    }

    public function to_user() {
        return $this->belongsTo('App\User');
    }

    public function item() {
        return $this->belongsTo('App\Items');
    }

} 