<?php
include_once('../base.php');
$order_id = $_GET['order_id'] ? $_GET['order_id'] : '';
$price = $_GET['price'] ? $_GET['price'] : '';
$OOPay = new OOPay();
$order = json_decode($OOPay->changePayment($order_id, $price));
echo json_encode($order);
?>