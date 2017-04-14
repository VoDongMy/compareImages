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

    protected $appends = ['thumbnail_img', 'duration_posted', 'distance'];

    public function user() 
    {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function getDistanceAttribute() 
    {
        // $geoCodeUser = getGeoCode($this->user->location);
        // $latitudeFrom = $outputFrom->results[0]->geometry->location->lat;
        // $longitudeFrom = $outputFrom->results[0]->geometry->location->lng;
        return '1,6 km';
    }
    public function getDurationPostedAttribute() 
    {
        return  getDurationAgo( $this->created_at );
    }

    public function category() 
    {
        return $this->belongsTo('App\Models\Category','cat_id');
    }

    public function getThumbnailImgAttribute()
    {
        $picture = $this->hasMany('App\Models\Pictures','item_id')->first();
        return empty($picture)? '' : $picture->thumbnail;
    }

    public function pictures()
    {
        return $this->hasMany('App\Models\Pictures','item_id');
    }
    
    public function likes()
    {
        return $this->morphToMany('App\Models\likes', 'likeable');
    }   

    public function watchs()
    {
        return $this->morphToMany('App\Models\Watchs', 'watchable');
    }
} 