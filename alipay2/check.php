<?php
include_once('../base.php');
$order_id = $_GET['name'] ? $_GET['name'] : '';
$method = $_GET['method'] ? $_GET['method'] : '';
$OOPay = new OOPay();

if ($method == 'queryBackupOrderStatus') {
  $order = json_decode($OOPay->getV2boardOrderStatus($order_id));
  echo json_encode($order);
} else {
  $order = json_decode($OOPay->getOrderStatus($order_id));
  echo json_encode($order);
}
?>