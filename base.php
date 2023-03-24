<?php
// load dependencies via Composer
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

include_once 'Logger.php';
class OOPay
{
  var $client;
  var $createUrl;
  var $checkUrl;
  var $getOrderDetailUrl;
  var $qrcUrl;
  var $payPageUrl;
  var $orderStatusURL;
  var $changePaymentURL;
  var $changePaymentForOOShopURL;
  var $getV2boardOrderStatusURL;

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
    $this->changePaymentURL = 'https://catcloud.in/api/v1/guest/order/checkout';
    $this->changePaymentForOOShopURL = 'https://ooshop.vip/changePayment/';
    $this->getV2boardOrderStatusURL = 'https://catcloud.in/api/v1/guest/order/status';
  }

  function createOrder($order)
  {
    $response = $this->client->post($this->createUrl, [
      'form_params' => $order,
      'timeout' => 15, //设置请求超时时间
      'verify' => false
    ]);
    try {
      $body = $response->getBody(); //获取响应体，对象
      $bodyStr = (string)$body; //对象转字串,这就是请求返回的结果
      return $bodyStr;
    } catch (PDOException $e) {
      $message = '
      <b>🚨 Error: getWechatQrCode</b>
      <code>' . $e->getMessage() . '</code>';
      $this->sendTGMessage($message);
    }
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

  function changePayment($order_id, $price)
  {
    $message = '
<b># User has switched payment</b>
Price: <code>￥' . $price . '</code>
OrderID: <code>' . $order_id . '</code>
';
    $this->sendTGMessage($message);

    try {
      $response = $this->client->post($this->changePaymentURL, [
        'form_params' => [
          'trade_no' => $order_id,
          'method' => 10
        ]
      ]);
      $body = $response->getBody();
      $bodyStr = (string)$body;
      return $bodyStr;
    } catch (PDOException $e) {
      Logger::error("ChangePaymentURL Request Error", [$e->getMessage()]);
      return json_encode(['error' => '订单已取消', 'code' => 500]);
    }
  }

  function changePaymentForOOShop($order_id, $price)
  {
    try {
      $response = $this->client->get($this->changePaymentForOOShopURL . $order_id);
      $body = $response->getBody();
      $bodyStr = (string)$body;
      return $bodyStr;
    } catch (PDOException $e) {
      Logger::error("changePaymentForOOShop Request Error", [$e->getMessage()]);
      return json_encode(['error' => '订单已取消', 'code' => 500]);
    }
  }

  function sendTGMessage($message)
  {
    $telegram = new Telegram('1971956101:AAHtw8r2Mxh-a7dbMOGff_q4PMif35NHUec');
    $content = ['chat_id' => 597591106, 'parse_mode' => 'html', 'text' => $message];
    $telegram->sendMessage($content);
  }

  function getV2boardOrderStatus($order_id)
  {
    try {
      $response = $this->client->post($this->getV2boardOrderStatusURL, [
        'form_params' => [
          'trade_no' => $order_id,
        ]
      ]);
      $body = $response->getBody();
      $bodyStr = (string)$body;
      return $bodyStr;
    } catch (PDOException $e) {
      Logger::fatal("Telegram Request Error", [$e->getMessage()]);
      return json_encode(['error' => '订单不存在', 'code' => 500]);
    }
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

    set_error_handler(
      function ($severity, $message, $file, $line) {

        $message = '
        <b>🚨 Error: getWechatQrCode</b>
        <code>' . $message . '</code>';
        $this->sendTGMessage($message);
        // throw new ErrorException('该支付通道发生故障，请取消订单重新尝试下单，或联系客服处理。', $severity, $severity, $file, $line);
      }
    );

    try {
      $html = file_get_contents($getHtmlFileName, false, stream_context_create($stream_opts));
    } catch (Exception $e) {
      echo $e->getMessage();
    }

    restore_error_handler();

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
