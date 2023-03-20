<?php
include_once('../base.php');
$order_id = $_GET['name'] ? $_GET['name'] : '';
$OOPay = new OOPay();
$order = json_decode($OOPay->getOrderStatus($order_id));

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

?>