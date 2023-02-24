<?php

// load dependencies via Composer
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

// $html = file_get_contents('https://ooopay.in//order/wechat/index.html');
// $html = file_get_contents('https://ooopay.in/order/download/fuck3.html');
// $regex = "#<script(.*?)>(.*?)</script>#is";
// preg_match_all($regex, $html, $scripts);
// $scriptsString = end(end($scripts));
// $scriptsStringArr = explode(';',$scriptsString);
// $scriptsStringArr = explode(',',$scriptsStringArr[0]);
// $qrcStr = str_replace("'","",$scriptsStringArr[3]);
// $qrcStr = str_replace(")","",$qrcStr);
// echo $qrcStr;
// var_dump($scriptsStringArr);


$url = 'https://pay.senhuo.cn/pay/Pay.php?appid=zp1652299771&out_trade_no=q6Y0lBkjCg9jXJOk&pay_type=wechat&redirect_url=https%3A%2F%2Fooshop.vip%2Fpay%2Fzpay%2Freturn_url%3Forder_id%3Dq6Y0lBkjCg9jXJOk&sub_notify_url=https%3A%2F%2Fooshop.vip%2Fpay%2Fzpay%2Fnotify_url&title=q6Y0lBkjCg9jXJOk&total=3900&sign=56aa8d798fbd7904b9107660544e60b7';

  function download($file_source, $file_target) {
    $rh = fopen($file_source, 'rb');
    $wh = fopen($file_target, 'w+b');
    if (!$rh || !$wh) {
        return false;
    }

    while (!feof($rh)) {
        if (fwrite($wh, fread($rh, 4096)) === FALSE) {
            return false;
        }
        echo ' ';
        flush();
    }

    fclose($rh);
    fclose($wh);

    return true;
  }

  var_dump(download($url, 'download/1.html'));


  // 随机选择支付
  // $payments = array(24, 29, 24); // from ooshop
  // $payments_keys = array_rand($payments);

  // echo $payments[$payments_keys];

exit;
// function download($file_source, $file_target) {
//   $rh = fopen($file_source, 'rb');
//   $wh = fopen($file_target, 'w+b');
//   if (!$rh || !$wh) {
//       return false;
//   }

//   while (!feof($rh)) {
//       if (fwrite($wh, fread($rh, 4096)) === FALSE) {
//           return false;
//       }
//       echo ' ';
//       flush();
//   }

//   fclose($rh);
//   fclose($wh);

//   return true;
// }

// $url = 'https://api.xunhupay.com/qrcode/201906146572.html?data=aHR0cHM6Ly9xci5hbGlwYXkuY29tL2JheDAxNzgxYmd5YzN1dXI3Z2Z3MzAyYQ==&nonce_str=6111589195&time=1655189191&hash=50645cf3dc982faffdcda99b5803ead6';

// var_dump(download($url, 'download/fuck2.png'));

exit;

class OOPay
{
  var $client;
  var $createUrl;
  var $checkUrl;
  var $getOrderDetailUrl;
  var $qrcUrl;
  var $payPageUrl;

  function __construct()
  {
    $this->client = new Client([
      'base_uri' => 'https://ooshop.vip',
      'timeout'  => 2.0,
      'verify' => false
    ]);
    $this->createUrl  = 'https://ooshop.vip/create-order-api/';
    $this->checkUrl   = 'https://ooshop.vip/check-order-status/';
    $this->getOrderDetailUrl   = 'https://ooshop.vip/detail-order-sn/';
    $this->qrcUrl     = 'https://qrc.hp.ooshop.vip/gePayQRC.php';
    $this->payPageUrl = 'https://ooshop.vip/pay-gateway/pay%252Fhpjalipay/hpjalipay/';
  }

  function createOrder($order)
  {
    $response = $this->client->post($this->createUrl, [
      'query' => $order,
      'timeout' => 15 //设置请求超时时间
    ]);
    $body = $response->getBody(); //获取响应体，对象
    $bodyStr = (string)$body; //对象转字串,这就是请求返回的结果
    return $bodyStr;
  }

  function searchOrderBySN($order_id) {
    $response = $this->client->get($this->getOrderDetailUrl . $order_id);
    $body = $response->getBody();
    $bodyStr = (string)$body;
    return $bodyStr;
  }

  function checkOrder($order_id)
  {
    $response = $this->client->get($this->checkUrl . $order_id);
    $body = $response->getBody(); //获取响应体，对象
    $bodyStr = (string)$body; //对象转字串,这就是请求返回的结果
    return $bodyStr;
  }

  function getQrc($order_id)
  {
    $response = $this->client->post($this->qrcUrl, [
      'form_params' => [
        'paymentUrl' => $this->payPageUrl . $order_id,
      ],
      'timeout' => 15
    ]);
    $body = $response->getBody(); //获取响应体，对象
    $bodyStr = (string)$body; //对象转字串,这就是请求返回的结果
    return json_decode($bodyStr);
  }
}

// 拿到订单号和邮箱即可
$money = $_GET['money'] ?$_GET['money'] : '';
$order_id =$_GET['name'] ?$_GET['name'] : '';
$notify_url =$_GET['notify_url'] ?$_GET['notify_url'] : '';
$return_url =$_GET['return_url'] ?$_GET['return_url'] : '';
$method =$_GET['method'] ?$_GET['method'] : '';

if (!$order_id) {
  die('404|Error');
}

$OOPay = new OOPay();

// 根据金额判断下哪个单
$gid = 1;

// 月付
if ($money > 30) {
  $gid = 1;
}
// 团 月
if ($money > 120) {

}
// 半年
if ($money > 180) {
  $gid = 3;
}
// 年
if ($money > 300) {
  $gid = 4;
}
