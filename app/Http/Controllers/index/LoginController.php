<?php

namespace App\Http\Controllers\index;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\IndexModel\LoginModel;
use Illuminate\Support\Facades\Redis;
use App\IndexModel\LoginModel as LoginModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use App\IndexModel\GithubModel;
class loginController extends Controller
{
    //注册页面视图显示
    public function register(){
        return view('login/register');
    }
    //执行注册
    public function registerdo(Request $request)
    {
        //接收传值
        $data = $request->all();
        //验证非空
        if (empty($data['user_name'])) {
            return redirect('login/register')->with('msg', '用户名不能为空');
        }
        if (empty($data['user_pwd'])) {
            return redirect('/login/register')->with('msg', '密码不能为空');
        }
        //密码一致
        if ($data['user_pwd'] != $data['user_pwd1']) {
            return redirect('/login/register')->with('msg', '密码不一致');
        }
        unset($data['user_pwd1']);
        $data['user_pwd'] = password_hash($data['user_pwd'], PASSWORD_DEFAULT);
        if (empty($data['user_tel'])) {
            return redirect('/login/register')->with('msg', '手机号不能为空');

        }
        if (empty($data['user_email'])) {
            return redirect('/login/register')->with('msg', '邮箱不能为空');
        }
        //注册的时间
        $data['reg_time'] = time();
//        $userInfo = LoginModels::insert($data);
//        //生成激活码
        $userInfo=$this->sendEmail($data['user_email']);
//        echo '进入邮箱激活';
//        $active_code = Str::random(64);
//        //保存激活码与用户的对应关系 使用有序集合
//        $redis_active_key = 'ss:user:active';
//        Redis::zAdd($redis_active_key,$userInfo,$active_code);
//        $active_url = env('APP_URL').'/login/active?code='.$active_code;
//        echo $active_url;echo "<br>";
//        echo "手动把地址输入地址栏上即可自动注册";
//        exit;
        if ($userInfo) {
            Redis::hMset($userInfo,$data);
            return view('login/yesemail');
//            return redirect('/login/login')->with('msg','注册成功');
        } else {
            return redirect('/login/register')->with('msg', '注册失败');
        }
    }
    //确认注册
    public function yesemail($email){
        $regInfo = Redis::hgetall($email);
        $userInfo = LoginModels::insert($regInfo);
        Redis::del($email);
        return view('/login/yesemail');

    }
    /**
     * 激活用户
     */
//    public function active(Request  $request)
//    {
//        $active_code = $request->get('code');
//        echo "激活码：".$active_code;echo '</br>';
//
//
//        $redis_active_key = 'ss:user:active';
//        $user_id = Redis::zScore($redis_active_key,$active_code);
//        if($user_id){
//            echo "user_id: ". $user_id;echo '</br>';
//            //激活用户
//            LoginModels::where(['user_id'=>$user_id])->update(['is_validated'=>1]);
////            echo "激活成功";
//            return redirect('/login/login')->with('msg','激活成功');
//            //删除集合中的激活码
//            Redis::zRem($redis_active_key,$active_code);
//        }else {
//            echo "链接已经失效";
//        }
//}


    //登陆页面视图显示
    public function login(){
        return view('login/login');
    }
    //执行登陆
    public function logindo(Request $request)
    {
        //登陆的ip
        $last_ip = $_SERVER['REMOTE_ADDR'];
        $data = $request->all();
//        dd($data);
        //验证非空
        if (empty($data['user_name'])) {
            return redirect('/login')->with('msg', '账号不能为空');
        }
        if (empty($data['user_pwd'])) {
            return redirect('/login')->with('msg', '密码不能为空');
        }
        //手机号或者邮箱或者用户名登陆(一条中的所有数据)
        $res = LoginModels::where(['user_name' => $data['user_name']])
            ->orwhere(['user_email' => $data['user_name']])
            ->orwhere(['user_tel' => $data['user_name']])
            ->first();
        if (empty($res)) {
            return redirect('/login')->with('msg', '账号不存在');
        }
        //检测用户是已经被锁定
        $key="login:count".$res['user_id'];
        //剩余时间
        $login_time=ceil(Redis::TTL('login_time:'.$key)/60);
        if(!empty($login_time)){
            return redirect('/login')->with('msg','已被锁定，剩余'.$login_time."分钟");
        }
        $count=Redis::get($key);
        if($count>=4){
            //设置过期的时间
            Redis::setex('login_time:'.$key,3600,Redis::get($key));
            return redirect('/login')->with('msg','错误达到了五次,已被锁定一小时');
        }
        if (password_verify($data['user_pwd'], $res['user_pwd'])) {
            //将用户登录的错误次数设置为null
            Redis::setex($key,1,Redis::get($key));
            //用户每一次登陆的时间
            $key="login:".$res['user_id'];
//            dd($key);
//            $haha=Redis::lsh($key,time());
//            $xixi=Redis::lrange($key,0,-1);
//            foreach($xixi as $k=>$v){
//                echo date('Y-m-d H:i:s',$v)."<br>";
//            }
            //存session
            session(['user_id'=>$res['user_id'],'user_name'=>$res['user_name'],'user_email'=>$res['user_email'],'user_tel'=>$res['user_tel']]);
            //如果密码正确了那就把登陆的ip、最后登陆的时间、登陆的次数存到数据库
            $loginInfo=['last_ip'=>$last_ip,'last_login'=>time(),'login_count'=>$res['login_count']+1];
            LoginModels::where('user_id',$res['user_id'])->update($loginInfo);
            return redirect('/center')->with('msg', '登陆成功');
        } else {
            /**
             *  10分钟内，用户连续输入密码错误超过5次，锁定用户 60分钟（禁止登录）。
             * 提示：
             * 使用Redis实现计数（incr）
             * 使用expire实现时间控制
             */
            //如果错误次数为空设置十分钟内"
            if(empty(Redis::get($key))){
                Redis::setex($key,600,Redis::get($key));
            }
            //设置错误的次数
            $num=Redis::incr($key);
            return redirect('/login')->with('msg', '错误次数为'.$num);
        }
    }
    public function sendEmail()
    {
        // 获取邮箱标题
        $title = Str::random(64);
        // 获取邮箱内容
        $content = " 亲爱的用户：
                        您好
                        您于".date('Y-m-d H:i')."注册品优购,点击以下链接，即可激活该帐号：
                        ".env('APP_URL')."/login/yesemail/".$title."
                        (如果您无法点击此链接，请将它复制到浏览器地址栏后访问)
                        1、为了保障您帐号的安全性，请在 半小时内完成激活，此链接将在您激活过一次后失效！
                        2、请尽快完成激活，否则过期，即 ".date('Y-m-d H:i',time()+60*30)." 后品优购将有权收回该帐号。
                        品优购";

        $toMail = '2856281442@qq.com';
        $flag=Mail::raw($content, function ($message) use ($toMail, $title) {
            $message->subject($title);
            $message->to($toMail);
        });
        if(!$flag){
            return $title;
        }else{
            return false;
        }
    }
    /**
     * GITHUB登录
     */
    public function callback()
    {
        //从地址栏 接收code
        $code = $_GET['code'];
        //换取access_token
        $token = $this->getAccessToken($code);
        //获取用户信息
        $git_user = $this->getGithubUserInfo($token);
        //判断用户是否已存在，不存在则入库新用户
        $u = GithubModel::where(['guid' => $git_user['id']])->first();
        if ($u)          //存在
        {
            //  登录逻辑
            $this->webLogin($git_user['id']);//$u->uid
        } else {          //不存在
            //在 用户主表中创建新用户  获取 uid
            $new_user = [
                'user_name' => Str::random(10)              //生成随机用户名，用户有一次修改机会
            ];
            $uid = LoginModel::insertGetId($new_user);
            // 在 github 用户表中记录新用户
            $info = [
                'uid' => $uid,       //作为本站新用户
                'guid' => $git_user['id'],         //github用户id
                'avatar' => $git_user['avatar_url'],
                'github_url' => $git_user['html_url'],
                'github_username' => $git_user['name'],
                'github_email' => $git_user['email'],
                'add_time' => time()
            ];
            $guid = GithubModel::insertGetId($info);        //插入新纪录
            $this->webLogin($git_user['id']);//$u->uid
            if ($guid) {
                return redirect('/')->with('msg', '登陆成功');
            }
        }
        return redirect('/')->with('msg','登陆成功');
    }
    //根据code 换取 token
    protected function getAccessToken()
    {
        $url = 'https://github.com/login/oauth/access_token';
        //post 接口  Guzzle or  curl
        $client = new Client();
        $response = $client->request('get', $url, [
            'verify' => false,
            'form_params' => [
                'client_id' => 'd536c76f634c4fcb8c7f',
                'client_secret' => '9a63e29b3e3b03e5c162ec8a403768a274c97224',
                'code' => $_GET['code'],
            ]
        ]);
        parse_str($response->getBody(), $str); // 返回字符串 、  	//access_token=59a8a45407f1c01126f98b5db256f078e54f6d18&scope=&token_type=bearer
        return $str['access_token'];
    }
    /*
     * 获取github个人信息
     * @param $token
     */
    protected function getGithubUserInfo($token)
    {
        $url = 'https://api.github.com/user';
        //GET 请求接口
        $client = new Client();
        $response = $client->request('GET', $url, [
            'verify' => false,
            //表信息
            'headers' => [
                'Authorization' => "token $token"
            ]
        ]);
        return json_decode($response->getBody(), true);
    }
    /*
     * WEB登录逻辑
     */
    protected function webLogin($guid)
    {
        //两表联查用户表和github用户表
        $res=githubModel::where('guid',$guid)
            ->leftjoin('user','user.user_id','=','p_users_github.uid')
            ->first();
        //将登录信息保存至session uid 与 token写入 seesion
        session(['user_id' =>$res['uid'],'user_name'=>$res['user_name']]);
    }
    public function a(){

    }
}

