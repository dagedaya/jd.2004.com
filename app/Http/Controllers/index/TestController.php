<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\IndexModel\MovieModel;
use App\IndexModel\SeatModel;
use Illuminate\Support\Facades\Redis;


class TestController extends Controller
{
    //电影购买系统视图
    public function movie(Request $request){
        $movie_id=$request->movie_id;
        $movieInfo=MovieModel::where('movie_id',$movie_id)->first();
        if(is_object($movieInfo)){
            $movieInfo=$movieInfo->toArray();
        }
        //剩余库存
        $movie_count=$movieInfo['movie_count'];
        $str=[];
        for($i=1;$i<=$movie_count;++$i){
            $str[]=[
                'seat_num'=>$i,
            ];
        }
        //根据电影id查询当前购买的座位号
        $seatInfo=SeatModel::where('movie_id',$movie_id)->get();
        if(is_object($seatInfo)){
            $seatInfo=$seatInfo->toArray();
        }
        $seat_num=[];
        foreach($seatInfo as $k=>$v){
            $seat_num[]=$v['seat_num'];
        }
        return view('test/movie',['movie_count'=>$str,'seat_num'=>$seat_num]);
    }

    //开始购票
    public function movieadd(Request $request){
        $user_id=session()->get('user_id');
        if(empty($user_id)){
            echo "<script>alert('请先登录');location.href='/login'</script>";exit;
        }
        $data=$request->except('_token');
        $movie_id=$data['movie_id'];
        if(empty($data['movie_count'])){
            echo "<script>alert('电影座不能为空');location.href='/test/movie?movie_id='+$movie_id;</script>";exit;
        }
        $movie_count=$data['movie_count'];
        //根据电影id查询当前电影已经购买购买当前座位号
        $seatInfo = SeatModel::where('movie_id',$movie_id)->get();
        if(is_object($seatInfo)){
            $seatInfo = $seatInfo->toArray();
        }
        $seat_num = [];
        foreach ($seatInfo as $k=>$v){
            $seat_num[] = $v['seat_num'];
        }
        //入库
        $data=[];
        foreach($movie_count as $k=>$v){
            if(in_array($v,$seat_num)){
                echo "<script>alert('$v+已被购买请重新选择');location.href='/test/movie?movie_id='+$movie_id;</script>";exit;
            }else{
                $data[]=[
                    'movie_id'=>$movie_id,
                    'seat_num'=>$v,
                    'add_time'=>time(),
                    'user_id'=>$user_id,
                ];
            }
        }
        $res=SeatModel::insert($data);
        if($res){
            echo "<script>alert('购买成功');location.href='/test/movie?movie_id='+$movie_id;</script>";exit;
        }else{
            echo "<script>alert('购买失败');location.href='/test/movie?movie_id='+$movie_id;</script>";exit;
        }
    }
    //递归(斐波那契数列第n项）
    public function fab()
    {
        $n = $_GET['n'];
        function Fibonacci($n)
        {
            //1 1 2 3 5 8 13 21......
            //如果第一位为1或者是第二位（都是1）
            if ($n == 1 || $n == 2) {
                return 1;
            }else{
                return Fibonacci($n - 2) + Fibonacci($n - 1);
            }
        }
        echo Fibonacci(2);
    }
}
