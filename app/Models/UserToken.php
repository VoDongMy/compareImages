<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model {

	protected $guarded = ['key'];

    public static function randomKey($size) {
        do {
            $key = openssl_random_pseudo_bytes ($size , $strongEnough);
        } while(!$strongEnough);
        $key = str_replace('+', '', base64_encode($key));
        $key = str_replace('/', '', $key);

        return base64_encode($key);
    }

    public static function getInstance() {
        $token = new UserToken();
        $token->key = UserToken::randomKey(32);
        return $token;
    }

    public static function userFor($token) {
        $token = UserToken::where('key', '=', $token)->first();
        if (empty($token)) return null;

        return User::where('id',$token->user_id)->where('status',1)->first();
    }

    public static function isUserToken( $user_id, $token ) {
        return UserToken::where('user_id', '=', $user_id)
            ->where('key', '=', $token)
            ->exists();
    }

    public function user() 
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
