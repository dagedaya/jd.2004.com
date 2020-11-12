<?php

namespace App\Http\Middleware;

use Closure;

class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    //handle相当于魔术方法（自动调用）
    public function handle($request, Closure $next)
    {
        //前置中间件
        $user_id=$request->session()->get('user_id');
        if(empty($user_id)){
            if(isset($_SERVER["HTTP_X_REQUESTED_WITH"])&&$_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest"){
                $data=[
                    'error'=>400003,
                    'msg'=>'请先登录',
                ];
                die(json_encode($data));
            }
            return redirect('/login')->with('msg','请先登录');
        }
        return $next($request);
        //后置中间件
    }
}
