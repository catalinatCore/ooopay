<?php
include_once('../base.php');
$order_id = $_GET['name'] ? $_GET['name'] : '';
$OOPay = new OOPay();
$order = json_decode($OOPay->getOrderStatus($order_id));
echo json_encode($order);
?>