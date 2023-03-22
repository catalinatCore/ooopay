<?php
include_once('../base.php');
$order_id = $_GET['order_id'] ? $_GET['order_id'] : '';
$OOPay = new OOPay();
$order = json_decode($OOPay->changePayment($order_id));
echo json_encode($order);
?>