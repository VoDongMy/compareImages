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
        return $this->belongsTo('App\Models\User');
    }

    public function getSettingsAttribute($value)
    {
    	$data = json_decode($value);
    	$dataDefault = json_decode('{"notify":{"new_exchange":0,"new_bids":0,"messages":0},"distance":"","low_price":{"low":0,"high":0}}');
        return empty($data)? $dataDefault : $data;
    }
} 