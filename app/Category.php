<?php
/**
 * Created by PhpStorm.
 * User: tantq
 * Date: 23/11/2015
 * Time: 01:48
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Category extends Model {

    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','price','is_exchange'];

    public function items()
    {
        return $this->hasMany('App\Items');
    }
} 