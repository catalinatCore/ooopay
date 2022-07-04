<?php
include_once('base.php');

$sign = $_GET['sign'] ? $_GET['sign'] : '';

$params = [
  'money' => $_GET['money'] ? $_GET['money'] : '',
  'name' => $_GET['name'] ? $_GET['name'] : '',
  'notify_url' => $_GET['notify_url'] ? $_GET['notify_url'] : '',
  'return_url' => $_GET['return_url'] ? $_GET['return_url'] : '',
  'out_trade_no' => $_GET['out_trade_no'] ? $_GET['out_trade_no'] : '',
  'pid' => $_GET['pid'] ? $_GET['pid'] : ''
];

echo $_SERVER['PHP_SELF'];
exit;
$OOPay = new OOPay();

if (!$params['money'] || !$sign) die('<hr>404|ERROR');

// 签名验证
if ($sign != $OOPay->sign($params)) {
  die('签名验证失败，请求参数已被篡改');
}

$order = json_decode($OOPay->checkOrder($params['name']));

// 已经过期
if ($order->code == 400001) {
  echo json_encode(['msg' => '订单已经过期', 'code' => 400000]);
}
// 订单等待支付
if ($order->code == 400000) {
  echo json_encode(['msg' => '订单等待支付', 'code' => 300]);
}
// 已支付
if ($order->code == 200) {
  echo json_encode(['msg' => '订单已支付', 'code' => 200]);
}
// 订单不存在
else {
  echo json_encode(['msg' => '订单不存在', 'code' => 500]);
}