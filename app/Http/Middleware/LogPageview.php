<?php

namespace App\Http\Middleware;

use Closure;
use App\IndexModel\PathModle;
class LogPageview
{
    /**
     * Handle an incoming request.
     * 纪录用户的访问纪录
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request_uri=$_SERVER["REQUEST_URI"];
//        //TODO 纪录到数据库
//        $data=[
//            'user_path'=>$request_uri,
//        ];
//        $path=PathModle::insert($data);
        return $next($request);
    }
}
