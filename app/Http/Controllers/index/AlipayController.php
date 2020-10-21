<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/8
 * Time: 20:19
 */

namespace App\Http\Controllers\index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Log;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;
class AlipayController extends Controller{

    protected $config = [
        'app_id' => '2021000116698145',//你创建应用的APPID
        'notify_url' => '',//异步回调地址
        'return_url' => 'https://2004dageda.wwwzy.top/index.php/cart/sync',//同步回调地址
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApsmciRcEueH0MnBrwRl0RgkhZEwx9Xg2AXK7250HmEamR2YUQUvqR/PfLCv3jjXM6M2lIJBxD2NO2WovrMZ/nhHesFSuN5Gl6IMKdnGaWmq3LcGLOzOMwx0NWHG5wp6hQ43ybhufdsAT7Kp0dtG14hKjolNUDwpyNTWKzb4A5b9qH/XsvkIE4npdXXZ8mdimVyb4HPHLDwXkpBh+UO7Aq8EbCqItBKAhf6Zw3xFh9kAuY4w/GXaP7VcHiAh7/Kz7xoCRRueKBJQpRTF90ff+02WhQHPw/8juLlQjpasj7higJ3Dy+TFcG8UD5d0xkzPynLkepVfmSgu2OTu4to1yJwIDAQAB',//是支付宝公钥，不是应用公钥,  公钥要写成一行,不要换行
        // 加密方式： **RSA2**
        'private_key' => 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCl/wLtMDCjTSBxpl4Ht8M8hn5gagij8H3C8y2mYa5gO1IaF/iAmHOcUzjKeo2gg3wiDy9EGsRO2nt30ixJcCV2TMLAQGlO4PUjrtjacaR52UJu05wzP9TuEAojXULUYcW7EPmMjNnmMDr/aWw8b/s/x+nqUhN/jU3/h2P2G0y30tbSh+VVHvxu5q/vbafyaprt/ZgFv/nvEdLAskKj8hVttGR3D3lA2o9Dluqxl/uDy2j/8Xb1po8GBqo+j1vwfh/mt7TdAmrtnjw1zFnCYkl1fbY26DNztVAufflGJRBpl15UXR1k2KPNaZCw5BYXPzOJYd4Z9EycqwS+TKjy17cvAgMBAAECggEAeXndUmZpsZfHnmP5e/xmy+xAn535JL/hyMDdL11clt/SfXX9TV5DfKsKbGKzMjwGo4YpONW9w1bv9AfCkRuYzrHt83MvnaHSw4I4/+MaUnFDxIbnUDnuQSlLwgWF2paSdQs/FDQfYez5v+AObUwluc86x1e5HSbzZYRXCst/oSVjHg46MWGrTBTPiTvsfkDX7wgspeiljQn8flG/i2P75D1TzU1J+BE+sSmSmqMSzLHdmU6edckWGKqdcft+d4iDuqBihSJNfVfGXOps5LKMcMTi4bY/7wxtaRMT1d3S4aMMwIjwZq8ENt8HPjY0ADv9AZB8S4wpbKAg5qJjs148gQKBgQDjHlg53ftZQ0GQT0jMXW589GtCry/KLMvO7cSDYbyuGqVoXjJVCmbBOu37pqV7AEvrcbPGShG1ItyurXER7w72/sNIdY+lILFaJ6LXmpQhZEcav2mwMISs0YYitYm90x0e/XxRtt/yYzrgGwWvoluGa1TGSlIjKx/Gjkn8pvn4iQKBgQC7GuDBM/Ks9hiHRjy9jPiVHWXyMZj2+ixXu3OsOovLSgq23NyBO3lc3CmpM72Fro9HLcELIf9JgG6+TqspUn0QNRiFp4THbNExM0QlHKm5tPgoOIuHNJrxZLzBNW22bbU6UvY6Jv4kuN2F2u6Sk4y03Av+goK/y0DIjKghvVDT9wKBgQCc8TAYNLvmb+JTfWYZyop6EFA6UWKdu5zOjmEYRKakTMd2OnlfaIOfDUC8f+ij+Y180iJfaHmaCNAkaLfUo7Rcm6mIXax4eAH2AaO2hxvLg0bbReAbnztfTJ37SCU5pjPeYV3R/aP9E/LwP9wLBQ9n8K0xJNRjdv5LL7ISw7PCuQKBgGmGVTBh/gIyoc7ZfDRjp2diNzcLZfwVSKEKZcjDFEjJL5XGps50HpSSzyRQvBkDRGeelHuU5wgrWUoX0Ezz6vkTGQt4WVioNKuNAGf17HuCZ1s32Omeb6ypZhOJ8KCs60NDuv89MqS/ZqCxw1ADy53NZS5bqSZGmfkB62lr/oQtAoGAUMKRGlc58II1QmPXgShdMekGvkf7SDAYcQC5sU3WkskeSTc9xTECYBMYIFC4gnJBEyhqzbUEZdIlKfYyVgvkXNsrSY/Gg38eLHgpoIRUZtSxRTEeVWnqcfU9tjus/NADIMRd3iYz2KXjTuJWcJ2IZyrh1RTUe+UQlwtbRn4KXho=',//密钥,密钥要写成一行,不要换行
        'log' => [ // optional
            'file' => './logs/alipay.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ];
    public function Alipay($order)
    {
//        $order = [
//            'out_trade_no' => time(),
//            'total_amount' => '0.1',
//            'subject' => 'test subject - 测试',
//        ];

        $alipay = Pay::alipay($this->config)->web($order);

        return $alipay;// laravel 框架中请直接 `return $alipay`
    }

    public function AliPayReturn()
    {
        $data = Pay::alipay($this->config)->verify(); // 是的，验签就这么简单！
        if(true){
            return view('pay/paysuccess');
        }
        // 订单号：$data->out_trade_no
        // 支付宝交易号：$data->trade_no
        // 订单总金额：$data->total_amount
    }

    public function AliPayNotify()
    {
        $alipay = Pay::alipay($this->config);

        try{
            $data = $alipay->verify(); // 是的，验签就这么简单！

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况

            Log::debug('Alipay notify', $data->all());
        } catch (\Exception $e) {
            //$e->getMessage();
        }

        return $alipay->success();// laravel 框架中请直接 `return $alipay->success()`
    }
}
