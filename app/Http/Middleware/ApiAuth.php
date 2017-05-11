<?php

namespace App\Http\Middleware;

use App\Helpers\DataLog;
use Closure;
use Config;

class ApiAuth
{
    protected $except = [
        'send-mail-confirm/'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $url = $request->url();

        $domain = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        if (strpos($domain, env('APP_URL')) !== false) {
            $url = str_replace('http://', 'https://', $url);
        };

        $query = $request->query();
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
            $url = urldecode($url);
        }
        $method = $request->method();
        $baseString = "{$method}:{$url}";

        DataLog::set('sign.log', $baseString);

        // API authenticate info
        $secretKey = env('API_SECRET');
        $apiKey = env('API_KEY');

        $signature = base64_encode(hash_hmac('sha512', $baseString, $secretKey, true));
        $requestApiKey = $request->header('Authenticate-Key');
        $requestSignature = $request->header('Authenticate-Signature');
        // back door for PostMan Dev

        if (md5($request->header('Back-Door')) == 'c1d77365ed8153733410bdcb2c679270') {
            return $next($request);
        }
        // API authenticate
        if ($requestApiKey != $apiKey || $requestSignature != $signature ) {
            return response()->json([
                    'status_code' => 401,
                    'messages'    => 'Unauthorized',
                    'data'        => array()
                    ],401);
        }

        return $next($request);

    }
}
