<?php

namespace App\Exceptions;

use Exception;
use App\Helpers\DataLog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $url = $request->url();
        $data = [
            'URL' => $url,
            'File' => $e->getFile(),
            'Line' => $e->getLine(),
            'Message' => $e->getMessage()
        ];
        // DataLog::logPublic('exception.log', $data, false);

        // if (strpos($url, '/api/version/')) {

        //     // Log error
        //     return response()->json([
        //             'status_code' => 500,
        //             'messages'    => 'server error',
        //                                     ], 400);
        // }
        // return parent::render($request, $e);
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }
        if (strpos($url, '/api/version/')) {

            // Log error
            return response()->json([
                    'status_code' => 500,
                    'messages'    => 'server error',
                    'data'        =>  $data   ], 400);
        }
        return parent::render($request, $e);

        return parent::render($request, $e);

    }
}
