<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 02:27
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Bids extends Model {

    protected $table = 'bids';

    protected $fillable = ['price_bidding','status'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function item() {
        return $this->belongsTo('App\Models\Items');
    }
} 