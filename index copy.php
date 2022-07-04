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
$money = $_GET['money'] ? $_GET['money'] : '';
$order_id = $_GET['name'] ? $_GET['name'] : '';
$notify_url = $_GET['notify_url'] ? $_GET['notify_url'] : '';
$return_url = $_GET['return_url'] ? $_GET['return_url'] : '';
$method = $_GET['method'] ? $_GET['method'] : '';

if ($method == 'query') {
  $OOPay = new OOPay();
  $order = json_decode($OOPay->checkOrder($order_id));
  // 已经过期
  if ($order->code == 400001) {
    echo json_encode(['msg' => 'expired....', 'code' => 400000]);
  }
  // 订单等待支付
  if ($order->code == 400000) {
    echo json_encode(['msg' => 'wait order', 'code' => 300]);
  }
  // 已支付
  if ($order->code == 200) {
    echo json_encode(['msg' => 'has paid order', 'code' => 200]);
  }
} else {

  if (!$order_id) {
    die('404|Error');
  }

  $OOPay = new OOPay();
  $order = json_decode($OOPay->checkOrder($order_id));

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

  //不存在
  // if ($order->code == 300000) {
  //   $order = [
  //     'gid' => $gid,
  //     'email' => 'oopay_customer@gmail.com',
  //     'payway' => 24,
  //     'price' => $money,
  //     'by_amount' => '1',
  //     'order_id' => $order_id
  //   ];
  //   $OOPay->createOrder($order);
  //   Header("Location: https://ooopay.in/order/alipay/"."?name=".$order_id."&money=".$money."&return_url=".$return_url);
  // }

  // 已经过期
  if ($order->code == 400001) {
    echo json_encode(['msg' => 'expired....', 'code' => 400000]);
  }

  // 订单等待支付
  if ($order->code == 400000) {
    echo json_encode(['msg' => 'wait order', 'code' => 300]);
    Header("Location: https://ooopay.in/order/alipay/"."?name=".$order_id."&money=".$money."&return_url=".$return_url);
  }

  // 已支付
  if ($order->code == 200) {
    echo json_encode(['msg' => 'has paid order', 'code' => 200]);
  }
}
