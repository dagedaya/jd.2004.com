<?php
/*
                   _ooOoo_
                  o8888888o
                  88" . "88
                  (| -_- |)
                  O\  =  /O
               ____/`---'\____
             .'  \\|     |//  `.
            /  \\|||  :  |||//  \
           /  _||||| -:- |||||-  \
           |   | \\\  -  /// |   |
           | \_|  ''\---/''  |   |
           \  .-\__  `-`  ___/-. /
         ___`. .'  /--.--\  `. . __
      ."" '<  `.___\_<|>_/___.'  >'"".
     | | :  `- \`.;`\ _ /`;.`/ - ` : | |
     \  \ `-.   \_ __\ /__ _/   .-` /  /
======`-.____`-.___\_____/___.-`____.-'======
                   `=---='
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
            佛祖保佑       永无BUG
*/
namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\IndexModel\WxUserModel;
use GuzzleHttp\Client;
use App\IndexModel\MediaModel;
use Log;
use Illuminate\Support\Str;
use App\IndexModel\GoodsModel;
class WxController extends Controller
{
    protected $users=[
        'onun_5lLzel17JvjX1tQiJH40eno',
    ];
    //微信接入
    public function checkSignature(Request $request)
    {
        $echostr = $request->echostr;
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = env('WX_TOKEN');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            //1.接收数据
            $xml_str = file_get_contents('php://input');
            //记录日志
            file_put_contents('wx_event.log',$xml_str,FILE_APPEND);
//            echo "$echostr";
//            die;
            //2.把xml文本转换成php的数组或者对象
            $data = simplexml_load_string($xml_str, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->data=$data;
            //判断该数据包是否是订阅的事件推送
            if(!empty($data)){
                $toUser = $data->FromUserName;//openid
                $fromUser = $data->ToUserName;
                //将聊天记录入库
                $msg_type=$data->MsgType;//推送事件的消息类型
                switch ($msg_type){
                    case 'event':
                        if($data->Event=='subscribe') {  //subscribe关注
                           echo $this->subscribehandler($data);
                            exit;
                        }elseif ($data->Event=='unsubscribe'){  //unsubscribe取关
                            echo $this->unsubscribehandler($data);
                            exit;
                        }elseif($data->Event=='CLICK'){  //一级菜单天气
                            $this->clickhandler($data);
                            switch ($data->EventKey){
                                case 'WEATHER';
                                    $content=$this->weather1();
                                    $toUser   = $data->FromUserName;
                                    $fromUser = $data->ToUserName;
                                    $result=$this->text($toUser,$fromUser,$content);
                                    return $result;
                                    break;
                                case "CHECKIN";  //二级签到菜单
                                    $key='CHECKIN'.date('Y-m-d',time());
                                    $content="签到成功";
                                    $touser_info=Redis::zrange($key,0,-1);//获取集合中的部分元素
                                    if(in_array((string)$toUser,$touser_info)){ //发送方账号
                                        $content="已经签到,不能重复";
                                    }else{
                                        Redis::zAdd($key,time(),(string)$toUser);//添加一个元素（添加一个发送方账号）
                                    }
                                    $result=$this->text($toUser,$fromUser,$content);
                                    return $result;
                                    break;
                                case "DAILY";  //二级每日推荐(商城商品)
                                    $recommend=GoodsModel::inRandomOrder()->take(1)->first()->toArray(); //随机从数据库获取数据
                                    $url="http://2004dageda.wwwhb.wenao.top/".'detail?id='.$recommend['goods_id'];
                                    //上传素材(下面有方法)
//                                    $PicUrl=$recommend['goods_img'];
                                    $PicUrl="http://2004dageda.wwwhb.wenao.top/static/img/ad.jpg";
                                    $title="每日推荐";
                                    $description=$recommend['keywords'];
                                    if(empty($description)){
                                        $description="衣服";
                                    }
                                    $result=$this->img_text($toUser,$fromUser,$title,$description,$PicUrl,$url);
                                    return $result;
                                    break;
                            }
                        }elseif ($data->Event=='VIEW'){  //菜单view事件
                            $this->viewhandler($data);
                        }
                        break;
                        case 'video':
                            $result = $this->videohandler($data);
                            return $result;
                        break;
                        case 'voice';
                            $result=$this->voicehandler($data);
                            return $result;
                        break;
                        case 'text';
                            $this->texthandler($data);
                        break;
                }
                //天气
                if(strtolower($data->MsgType) == "text"){
//                   file_put_contents('wx_text.log',$data,'FILE_APPEND');
//                    echo "";
//                    die;
                    switch ($data->Content){
                        case "天气":
                            $category=1;
                            $content=$this->weather1();
//                            $key='4e268e1bc28d4d2a9223e11a55b9dab5';
//                            $url="https://devapi.qweather.com/v7/weather/now?location=101010100&key=".$key."&gzip=n";
//                            $api=file_get_contents($url);
//                            $api=json_decode($api,true);
//                            $content = "天气状态：".$api['now']['text'].'
//                                风向：'.$api['now']['windDir'];
                            break;
                        case "时间";
                            $category=1;
                            $content=date('Y-m-d H:i:s',time());
                            break;
                        default:
                            $category = 1;
                            $content  = "啊，亲，我疯了，你在说什么";
                            break;
                    }
                    $toUser   = $data->FromUserName;
                    $fromUser = $data->ToUserName;
                    if($category==1){
                        $template = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
                        $info = sprintf($template, $toUser, $fromUser, time(),'text',$content);
                        return $info;
                    }
                }
                //微信素材库(图片)
                if(strtolower($data->MsgType)=='image'){
                    //下载
                    $token=$this->access_token();
                    $media_id=$data->MediaId;
                    $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=".$token."&media_id=".$media_id;
                    $image=file_get_contents($url);
                    $local_path="static/images/".Str::random(111,222).".jpg";
                    $local=file_put_contents($local_path,$image);
                    if($local){
                        $media=MediaModel::where('media_url',$data->PicUrl)->first();
                        if(empty($media)){
                            $data=[
                                'media_url'=>$data->PicUrl,//图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200
                                'media_type'=>'image',//类型为图片
                                'add_time'=>time(),
                                'openid'=>$data->FromUserName,
                                'media_id'=>$data->MediaId,
                                'local_path'=>$local_path,
                            ];
                            MediaModel::insert($data);
                            $content="图片已存到素材库";
                        }else{
                            $content="素材库已经有了";
                        }
                        $result=$this->text($toUser,$fromUser,$content);
                        return $result;
                    }
                }
            }
        } else {
            return false;
        }
    }
    //关注
    protected function subscribehandler($data){
        $toUser = $data->FromUserName;//openid
        $fromUser = $data->ToUserName;
        $msgType = 'text';
        $content = '欢迎关注了我';
        //根据OPENID获取用户信息（并且入库）
        //1.获取openid
        $token=$this->access_token();
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$token."&openid=".$toUser."&lang=zh_CN";
        file_put_contents('user_access.log',$url);
        $user=file_get_contents($url);
        $user=json_decode($user,true);
        $wxuser=WxUserModel::where('openid',$user['openid'])->first();
        if(!empty($wxuser)){
            $content="欢迎回来";
        }else{
            $data=[
                'subscribe'=>$user['subscribe'],
                'openid'=>$user['openid'],
                'nickname'=>$user['nickname'],
                'sex'=>$user['sex'],
                'city'=>$user['city'],
                'country'=>$user['country'],
                'province'=>$user['province'],
                'language'=>$user['language'],
            ];
            $data=WxUserModel::insert($data);///
        }
        //%s代表字符串(发送信息)
        $template = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
        $info = sprintf($template, $toUser, $fromUser, time(), $msgType, $content);
        echo $this->create_menu();
        return $info;
    }
    //取关
    protected function unsubscribehandler($data){

    }
    //视频
    protected function videohandler($data){
//        dd($data);
        $toUser = $data->FromUserName;//openid
        $fromUser = $data->ToUserName;
        //下载
        $token=$this->access_token();
        $media_id=$data->MediaId;
        $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=".$token."&media_id=".$media_id;
        $image=file_get_contents($url);
        $local_path="static/video/".Str::random(111,222).".mp4";
        $local=file_put_contents($local_path,$image);
        if($local){
            $video=MediaModel::where('media_id',$data->MedisId)->first();
            if(empty($video)){
                //入库
                $video_info=[
                    'add_time'=>$data->CreateTime,
                    'media_type'=>$data->MsgType,
                    'media_id'=>$data->MediaId,
                    'msg_id'=>$data->MsgId,
                    'local_path'=>$local_path,
                ];
                MediaModel::insert($video_info);
                $content="视频已存入素材库";
//                echo __LINE__;
            }else{
                $content="素材库已经有了";
            }
            $result=$this->text($toUser,$fromUser,$content);
            return $result;
        }
    }
    //音频
    protected function voicehandler($data){
        //下载
        $toUser = $data->FromUserName;//openid
        $fromUser = $data->ToUserName;
        //下载
        $token=$this->access_token();
        $media_id=$data->MediaId;
        $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=".$token."&media_id=".$media_id;
        $image=file_get_contents($url);
        $local_path="static/voice/".Str::random(111,222).".amr";
        $local=file_put_contents($local_path,$image);
        if($local){
            $voice=MediaModel::where('media_id',$data->MedisId)->first();
            if(empty($voice)){
                $data=[
                    'add_time'=>$data->CreateTime,
                    'media_type'=>$data->MsgType,
                    'media_id'=>$data->MediaId,
                    'msg_id'=>$data->MsgId,
                    'local_path'=>$local_path,
                ];
                MediaModel::insert($data);
                $content="音频已存入素材库";
            }else{
                $content="音频已经有了";
            }
            $result=$this->text($toUser,$fromUser,$content);
            return $result;
        }
    }
    //文本
    protected function texthandler($data){
        $data=[
            'add_time'=>$data->CreateTime,
            'media_type'=>$data->MsgType,
            'openid'=>$data->FromUserName,
            'msg_id'=>$data->MsgId,
        ];
        MediaModel::insert($data);
    }
    //菜单click点击事件
    protected function clickhandler($data){
        $data=[
            'add_time'=>$data->CreateTime,
            'media_type'=>$data->Event,
            'openid'=>$data->FromUserName,
        ];
        MediaModel::insert($data);
    }
    //菜单view事件
    protected function viewhandler($data){
        $data=[
            'add_time'=>$data->CreateTime,
            'msg_id'=>$data->MenuId,
            'media_type'=>$data->Event,
            'openid'=>$data->FromUserName,
        ];
        MediaModel::insert($data);
    }
    /**
     * 1 回复文本消息
     * @param $toUser
     * @param $fromUser
     * @param $content
     * @return string
     */
    private function text($toUser,$fromUser,$content)
    {
        $template = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
        $info = sprintf($template, $toUser, $fromUser, time(), 'text', $content);
        return $info;
    }

    /**
     * 2 每日推荐
     * @param $toUser
     * @param $fromUser
     * @param $title
     * @param $description
     * @param $content
     * @param $url
     * @return string
     */
    private function img_text($toUser, $fromUser, $title, $description, $PicUrl, $url){
        $template="<xml>
              <ToUserName><![CDATA[%s]]></ToUserName>
              <FromUserName><![CDATA[%s]]></FromUserName>
              <CreateTime>%s</CreateTime>
              <MsgType><![CDATA[%s]]></MsgType>
              <ArticleCount>%s</ArticleCount>
              <Articles>
                <item>
                  <Title><![CDATA[%s]]></Title>
                  <Description><![CDATA[%s]]></Description>
                  <PicUrl><![CDATA[%s]]></PicUrl>
                  <Url><![CDATA[%s]]></Url>
                </item>
              </Articles>
            </xml>";
        $info=sprintf($template,$toUser,$fromUser,time(),'news',1,$title,$description,$PicUrl,$url);
        return $info;
    }
    /**
     * 4 回复视频消息
     * @param $toUser
     * @param $fromUser
     * @param $content
     * @param $title
     * @param $description
     * @return string
     */
    private function video($toUser,$fromUser,$content,$title,$description)
    {
        $template = "<xml>
                              <ToUserName><![CDATA[%s]]></ToUserName>
                              <FromUserName><![CDATA[%s]]></FromUserName>
                              <CreateTime><![CDATA[%s]]></CreateTime>
                              <MsgType><![CDATA[%s]]></MsgType>
                              <Video>
                                <MediaId><![CDATA[%s]]></MediaId>
                                <Title><![CDATA[%s]]></Title>
                                <Description><![CDATA[%s]]></Description>
                              </Video>
                            </xml>";
        $info = sprintf($template, $toUser, $fromUser, time(), 'video', $content,$title,$description);
        return $info;
    }
    /**
     * 获取access_token并缓存
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function access_token(){
        $key="access_token:";
        //判断是否有缓存
        $token=Redis::get($key);
        if($token){
//            echo "有缓存";
//            echo $token;
        }else{
//            echo "无缓存";
            $url= "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSECRET')."";
//            $response=file_get_contents($url);
            //使用guzzl发送get请求
            $client=new Client();//实例化客户端
            $response=$client->request('GET',$url,['verify'=>false]);//发起请求并接收响应    ssl
            $json_str=$response->getBody();//服务器的响应数据
            $data=json_decode($json_str,true);
            $token=$data['access_token'];
            //存到redis中
            Redis::set($key,$token);
            //设置过期时间
            Redis::expire($key,7200);   //两小时
        }
        return $token;
    }

    /**
     * 天气
     * @return string
     */
    public function weather1(){
        $url='http://api.k780.com:88/?app=weather.future&weaid=heze&&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json';
        $weather=file_get_contents($url);
        $weather=json_decode($weather,true);
        if($weather['success']){
            $content="";
            foreach ($weather['result'] as $v){
                $content.='日期：'.$v['days'].$v['week'].'当日温度：'.$v['temperature'].'天气：'.$v['weather'].'风向：'.$v['wind'];
            }
        }
        Log::info('===='.$content);
        return $content;
    }

    /**
     * 上传素材
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function guzzle2(){
        $access_token=$this->access_token();
        $type="image";
        $url="https://api.weixin.qq.com/cgi-bin/media/upload?access_token=".$access_token."&type=".$type." ";
        $client=new Client();//实例化客户端
        $response=$client->request('POST',$url,[
            'verify'=>false,
            'multipart'=>[
                [
                    'name'=>'media',
                    'contents'=>fopen('大海.jpg','r')
                ]   //上传的文件路径
            ]
        ]);  //发送请求并接收响应
        $data=$response->getBody();//服务器的响应数据
//        $media_id=json_decode($data,true);
        echo $data;
    }

    /**
     * 自定义菜单
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create_menu(){
        //获取access_token
        $access_token=$this->access_token();
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $array=[
            'button'=>[
                [
                    'type'=>'view',
                    'name'=>"商城1",
                    'url'=>'http://2004dageda.wwwhb.wenao.top'.'/web_auth',
                ],
                [
                    'type'=>'click',
                    'name'=>"天气",
                    'key'=>'WEATHER'
                ],
                [
                    'name'=>"菜单",
                    "sub_button"=>[
                        [
                            'type'=>'view',
                            'name'=>'百度',
                            'url'=>'https://www.baidu.com'
                        ],
                        [
                            'type'  => 'click',
                            'name'  => '签到',
                            'key'   => 'CHECKIN'
                        ],
                        [
                            'type'=>'click',
                            'name'=>'每日推荐',
                            'key'=>'DAILY',
                        ],
                    ]
                ],
            ]
        ];
//        $a=json_encode($array,JSON_UNESCAPED_UNICODE);
//        dd($a);
        $client=new Client();
        $response=$client->request('POST',$url,[
            'verify'=>false,
            'body'=>json_encode($array,JSON_UNESCAPED_UNICODE),
        ]);
        $data=$response->getBody();
        return $data;
    }
    /**
     * 微信网页授权
     * https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxfbad01063a92db24&redirect_uri=http://2004dageda.wwwhb.wenao.top/web_redirect&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect
     */
    public function wxWebAuth(){
        $redirect='http://2004dageda.wwwhb.wenao.top/'.'/web_redirect';
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".env('WX_APPID')."&redirect_uri=".$redirect."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
        return redirect($url);
    }
    /**
     * 微信网页授权后跳转地址
     */
    public function wxWebRedirect(){
        $code=$_GET['code'];
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".env('WX_APPID')."&secret=".env('WX_APPSECRET')."&code=".$code."&grant_type=authorization_code";
        $xml=file_get_contents($url);
        $xml_code=json_decode($xml,true);
        if(isset($xml_code['errcode'])){
            if($xml_code['errcode']==40163){
                return "验证码已经失效";
            }
        }
        $access_token=$xml_code['access_token'];
        $openid=$xml_code['openid'];
        //拉取用户的信息
        $api="https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $user=file_get_contents($api);
        $user_info=json_decode($user,true);
//        dd($user_info);
        if($user_info){
            return redirect('/');
        }
    }
    /**
     * 客服发送消息
     * POST请求
     */
    public function service(){
        $access_token=$this->access_token();
        dd($access_token);
//        echo $access_token;die;
        $url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token."";
        $client=new Client();
        foreach($this->users as $k=>$v){
            //文本
//            $data = [
//                'touser' => $v,
//                'msgtype' => 'text',
//                'text' => [
//                    'content' => 'shabiwuyanhui'
//                ]
//            ];
            //视频
//            $data=[
//                'touser'=>$v,
//                'msgtype'=>'video',
//                'video'=>[
//                    'media_id'=>'5MgKg4feEB-ydef4rmo1eQz50-bKYObccFoJf9xFuS-NSRGXXaW5aQHO-Azrownm',
//                    'title'=>"永不失联的爱",
//                    'description'=>"珍惜",
//                ],
//            ];
            //音乐
            $data=[
                'touser'=>$v,
                'msgtype'=>"music",
                'music'=>[
                    'title'=>'云与海',
                    'description'=>'嗯呐',
//                    'musicurl'=>
                    
                ],
            ];
            $respones=$client->request('POST',$url,[
                'verify'=>false,
                'body'=>json_encode($data,JSON_UNESCAPED_UNICODE),
            ]);
            echo $respones->getBody();
        }
    }





/**
 * 测试
 */
//    //测试
//    public function weather(){
//        //天气
//        $key='4e268e1bc28d4d2a9223e11a55b9dab5';
//        $url="https://devapi.qweather.com/v7/weather/now?location=101010100&key=".$key."&gzip=n";
//        $api=file_get_contents($url);
//        $api=json_decode($api,true);
//        $content = "天气状态：".$api['now']['text'].'风向：'.$api['now']['windDir'];
////        echo $content;
//        //openid
//        $openid=$this->access_token();
//        echo $openid;
//    }
//    //测试（postman）get
//    public function test2(){
//        print_r($_GET);
//    }
//    //测试post(form-data)
//    public function test3(){
//        print_r($_POST);
//    }
//    //测试post(raw)
//    public function test4(){
//        $xml_str=file_get_contents('php://input');
//        $data = simplexml_load_string($xml_str, 'SimpleXMLElement', LIBXML_NOCDATA);
//        echo $data->ToUserName;
//    }
//测试下载素材
//    public function test5(){
//        $token=$this->access_token();
//        $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=".$token."&media_id=iQyRyJg_jFEz6kjSsoaNC6uSyuL3m2vPc7PKUVCoc43QnzB0_ZpTtHwH2ZH_YFaE";
//        $image=file_get_contents($url);
//        $local_path='static/images/mmbiz_jpg';
//        $local=file_put_contents($local_path,$image);
//        var_dump($local);
//    }
    //测试用户信息
//    public function test(){
//        $toUser="abc";
//        $token=$this->access_token();
////        echo $token;die;
//        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$token."&openid=".$toUser."&lang=zh_CN";
//        echo $url;
//    }
    //测试每日推荐
//    public function daily($data){
//        $toUser = $data->FromUserName;//openid
//        $fromUser = $data->ToUserName;
//        $template="<xml>
//              <ToUserName><![CDATA[".$toUser."]]></ToUserName>
//              <FromUserName><![CDATA[".$fromUser."]]></FromUserName>
//              <CreateTime>".time()."</CreateTime>
//              <MsgType><![CDATA[news]]></MsgType>
//              <ArticleCount>1</ArticleCount>
//              <Articles>
//                <item>
//                  <Title><![CDATA[每日推荐]]></Title>
//                  <Description><![CDATA[每日推荐]]></Description>
//                  <PicUrl><![CDATA[http://2004dageda.wwwhb.wenao.top/static/img/ad.jpg]]></PicUrl>
//                  <Url><![CDATA[http://2004dageda.wwwhb.wenao.top/detail?id=217]]></Url>
//                </item>
//              </Articles>
//            </xml>";
//        return $template;
//    }
}
