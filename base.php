<?php
// load dependencies via Composer
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

class OOPay
{
  var $client;
  var $createUrl;
  var $checkUrl;
  var $getOrderDetailUrl;
  var $qrcUrl;
  var $payPageUrl;
  var $orderStatusURL;

  function __construct()
  {
    $this->client = new Client([
      'base_uri' => 'https://ooshop.vip',
      'timeout'  => 2.0,
      'verify' => false
    ]);
    $this->createUrl  = 'https://ooshop.vip/create-order-api/';
    $this->checkUrl   = 'https://ooshop.vip/check-order-status/';
    $this->getOrderDetailUrl   = 'https://ooshop.vip/search-order-by-sn/';
    $this->qrcUrl     = 'https://qrc.hp.ooshop.vip/gePayQRC.php';
    $this->payPageUrl = 'https://ooshop.vip/pay-gateway/pay%252Fhpjalipay/hpjalipay/';
    $this->orderStatusURL = 'https://ooshop.vip/pay/alipayOrder/';
  }

  function createOrder($order)
  {
    $response = $this->client->post($this->createUrl, [
      'form_params' => $order,
      'timeout' => 15, //设置请求超时时间
      'verify' => false
    ]);
    $body = $response->getBody(); //获取响应体，对象
    $bodyStr = (string)$body; //对象转字串,这就是请求返回的结果
    return $bodyStr;
  }

  function checkOrder($order_id)
  {
    $response = $this->client->get($this->checkUrl . $order_id);
    $body = $response->getBody(); //获取响应体，对象
    $bodyStr = (string)$body; //对象转字串,这就是请求返回的结果
    return $bodyStr;
  }

  function getOrderStatus($order_id)
  {
    $response = $this->client->get($this->orderStatusURL . $order_id);
    $body = $response->getBody(); //获取响应体，对象
    $bodyStr = (string)$body; //对象转字串,这就是请求返回的结果
    return $bodyStr;
  }

  function sign($params)
  {
    ksort($params);
    reset($params);
    $str = stripslashes(urldecode(http_build_query($params))) . 321;
    return md5($str);
  }

  function getGid($price)
  {
    // 根据金额判断下哪个单
    $gid = 1;
    // 月付
    if ($price > 30) {
      $item1 = array(1, 9, 14);
      $item1_keys = array_rand($item1);
      $gid = $item1[$item1_keys];
    }
    // 团 月
    if ($price > 120) {
      $item1 = array(10, 5);
      $item1_keys = array_rand($item1);
      $gid = $item1[$item1_keys];
    }
    // 半年
    if ($price > 180) {
      $item1 = array(3, 12);
      $item1_keys = array_rand($item1);
      $gid = $item1[$item1_keys];
    }
    // 年
    if ($price > 300) {
      $item1 = array(4, 15);
      $item1_keys = array_rand($item1);
      $gid = $item1[$item1_keys];
    }
    return $gid;
  }

  function getWechatQrCode($order_id)
  {
    $getHtmlFileName = 'https://ooshop.vip/uploads/html/' . $order_id . '.html';

    $stream_opts = [
      "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
      ]
    ];

    $html = file_get_contents($getHtmlFileName, false, stream_context_create($stream_opts));

    $regex = "#<script(.*?)>(.*?)</script>#is";
    preg_match_all($regex, $html, $scripts);
    $scriptsString = end(end($scripts));
    $scriptsStringArr = explode(';', $scriptsString);
    $scriptsStringArr = explode(',', $scriptsStringArr[0]);
    $qrcStr = str_replace("'", "", $scriptsStringArr[3]);
    $qrcStr = str_replace(")", "", $qrcStr);

    return $qrcStr;
  }

  // function gotoPaymentPage($params) {
  //   Header("Location: https://ooopay.in/order/alipay/"."?name=".$params['name']."&money=".$params['money']."&return_url=".$params['return_url']);
  // }

}
