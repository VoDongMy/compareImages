<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'first_name','last_name'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    //protected $hidden = ['password', 'remember_token'];
    public function login($user_id=null) {
        $token = '';
        UserToken::where('user_id', '=', $user_id)->delete();
        $user = User::where('id',$user_id)->where('status',1)->first();
        if ($user) {
            $token               = UserToken::getInstance();
            $token->user_id      = $this->id;
            $token->device_token = '';
            $token->save();
            $token->user = $user;
        }
        return $token;
    }

    public function logout($userId = 0) {
        UserToken::where('user_id', '=', $userId)->delete();
        return true;
    }
}
