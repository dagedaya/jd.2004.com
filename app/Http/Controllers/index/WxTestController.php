<?php

namespace App\Http\Controllers\index;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Log;
class WxTestController extends Controller
{
    //测试guzzle发送get请求
    public function guzzle1(){
        $url= "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSECRET')."";
        //使用guzzl发送get请求
        $client=new Client();//实例化客户端
        $response=$client->request('GET',$url,['verify'=>false]);//发起请求并接收响应
        $json_str=$response->getBody();//服务器的响应数据
        echo $json_str;
    }
    //测试上传素材
    public function guzzle2(){
        $access_token="";
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
//        echo $data;
        $content=$this->weather();
    }
    //测试天气780
    public function weather(){
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
        echo $content;
    }

}
