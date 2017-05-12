<?php

namespace App\Http\Middleware;

use Closure;
use Config;
use Illuminate\Support\Facades\Auth;
use App\Models\UserToken;
use App\Helpers\DataLog;


class ApiAuthenticate
{
    public function __construct(UserToken $userToken)
    {
        $this->userToken = $userToken;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        DataLog::logPublic('request.log', $request->url());
         DataLog::logPublic('request.log', $request->all());
        if ($request->header('User-Token')) {
            $token = $this->userToken->where('key',$request->header('User-Token'))->first();
            if ($token)
                return $next($request);
            
        }
        return response()->json([
                    'status_code' => 401,
                    'messages'    => 'Unauthorized',
                    'data'        => array()
                    ],401);
    }
}
