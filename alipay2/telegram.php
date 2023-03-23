<?php

include(__DIR__ . '../../vendor/autoload.php');

$telegram = new Telegram('1971956101:AAHtw8r2Mxh-a7dbMOGff_q4PMif35NHUec');
$order_id = '865656656';
$message = '
<b># User Change payment</b>
OrderID: <code>'.$order_id.'</code>
';
$content = ['chat_id' => 597591106, 'parse_mode' => 'html', 'text' => $message];
$telegram->sendMessage($content);
