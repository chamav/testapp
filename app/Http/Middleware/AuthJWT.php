<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Http\Middleware;

class AuthJWT extends Middleware\BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if ($this->auth->parser()->setRequest($request)->hasToken()) {
            try {
                $request->user = $this->auth->parseToken()->authenticate();
                if(!$request->user){
                    return response()->json(['success'=> false, 'error'=> ['common' => trans('validation.exists_db', ['attribute' => 'user']), 'code' => 404]]);
                }
            } catch (Exception $e) {
                if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){

                    return response()->json(['success'=> false, 'error'=> ['common' => 'Token is Invalid', 'code' => 403]], 403);

                }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){

                    return response()->json(['success'=> false, 'error'=> ['common' => 'Token is Expired', 'code' => 403]], 403);

                }else{

                    return response()->json(['success'=> false, 'error'=> ['common' => 'Something is wrong', 'code' => 500]], 500);

                }
            }
        }else
        { return response()->json(['success'=> false, 'error'=> ['common' => 'Token is required', 'code' => 403]], 403); }

        return $next($request);

    }
}
