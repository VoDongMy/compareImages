<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model {

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
        $token = new Token();
        $token->key = Token::randomKey(32);
        return $token;
    }

    public static function userFor($token) {
        $token = Token::where('key', '=', $token)->first();
        if (empty($token)) return null;

        return User::where('id',$token->user_id)->where('status',1)->first();
    }

    public static function isUserToken( $user_id, $token ) {
        return Token::where('user_id', '=', $user_id)
            ->where('key', '=', $token)
            ->exists();
    }
}
