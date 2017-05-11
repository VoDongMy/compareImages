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
    public function login($userId = 0, $parameter = array('udid'=>'0', 'device_token'=>'0')) {
        $token = (object)[];
        UserToken::where('user_id', $userId)->delete($userId);
        $user = User::where('status',1)->find($userId);
        if ($user) {
            $token               = UserToken::getInstance();
            $token->user_id      = $userId;
            // $token->device_token = $parameter['udid'];
            // $token->device_type = $parameter['device_type'];
            $token->save();
            $token->user = $user;
        }
        return $token;
    }

    public function logout($userId = 0) {
        UserToken::where('user_id', '=', $userId)->delete();
        return true;
    }

    public function likes()
    {
        return $this->morphToMany('App\Models\Likes', 'likeable');
    }

    public function watchs()
    {
        return $this->morphToMany('App\Models\Watch', 'watchable');
    }

    public function userGroupChats()
    {
        return $this->hasMany('App\Models\UserGroupChat', 'user_id');
    }
}
