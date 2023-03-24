<?php

include_once('../base.php');

$sign = $_GET['sign'] ? $_GET['sign'] : '';
$method = $_GET['method'] ? $_GET['method'] : '';

/*
https://ooopay.in/order/wechat/?money=129&name=2023030517031827224&
  notify_url=https://catcloud.in/api/v1/guest/payment/notify/OOPay/NPyQaIel
  &out_trade_no=2023030517031827224&pid=123
  &return_url=https://catcloud.in/#/order/2023030517031827224
  &sign=5e7c0fd876318a37b60d626114ec18c1
  &sign_type=MD5
*/

$params = [
  'money' => $_GET['money'] ? $_GET['money'] : '',
  'name' => $_GET['name'] ? $_GET['name'] : '',
  'notify_url' => $_GET['notify_url'] ? $_GET['notify_url'] : '',
  'return_url' => $_GET['return_url'] ? $_GET['return_url'] : '',
  'out_trade_no' => $_GET['out_trade_no'] ? $_GET['out_trade_no'] : '',
  'pid' => $_GET['pid'] ? $_GET['pid'] : ''
];

$OOPay = new OOPay();


// Change payment method
if ($method == 'changePayment') {
  $OOPay->changePaymentForOOShop($params['name'], $params['money']);
} else {
  // 缺少参数
  if (!$params['money'] || !$sign) die('<hr>404|ERROR');
  // 签名验证
  if ($sign != $OOPay->sign($params)) {
    die('签名验证失败，请求参数已被篡改');
  }
}

$order = json_decode($OOPay->checkOrder($params['name']));

//不存在
if ($order->code == 300000) {

  // 随机选择支付
  $payments = array(30); // 30 = 微信 for me
  $payments_keys = array_rand($payments);

  // 创建订单
  $order = [
    'gid' => $OOPay->getGid($params['money']),
    'email' => 'oopay_customer@gmail.com',
    'payway' => $payments[$payments_keys],
    'price' => $params['money'],
    'by_amount' => 1,
    'order_id' => $params['name']
  ];
  $OOPay->createOrder($order);
}

$qrcStr = $OOPay->getWechatQrCode($params['name']);
$returnURl = 'https://catcloud.in/#/order';
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>微信支付收银台</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
  <script type="text/javascript" src="/order/wechat/assets/js/jquery.min.js"></script>
  <script type="text/javascript" src="/order/wechat/assets/js/jquery.qrcode.min.js"></script>
  <script type="text/javascript" src="/order/wechat/assets/js/function.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.14.3/dist/css/uikit.min.css" />
</head>

<body>

  <div class="uk-container uk-container-xsmall uk-margin-xlarge-top uk-margin-remove-top@s">
    <div class="uk-card uk-card-default uk-grid-collapse uk-child-width-1-1@s uk-margin" uk-grid>
      <h4 class="uk-heading-bullet uk-padding">微信支付收银台</h4>
      <div class="uk-card-media-left uk-cover-container">
        <div id="qrcode" uk-cover></div>
        <canvas width="200" height="200"></canvas>
      </div>
      <div>
        <div class="uk-card-body uk-text-center">
          <h3 class="uk-card-title uk-text-default">
            <span class="uk-text-default uk-text-danger">¥</span>
            <span class="uk-text-large uk-text-danger" style="font-size: 2.5em;"><?php echo $_GET['money']; ?></span>
          </h3>
          <div class="title">订单号: <?php echo $_GET['name']; ?></div>
          <hr>
          <p>请使用微信扫一扫完成支付</p>
          <p id="mobile-tips" class="uk-text-danger uk-hidden">📌 微信不支持截图至相册扫码，请使用备用手机或电脑打开后扫码</p>
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript">
    (function($) {
      var order_id = <?php echo json_encode($_GET['name']); ?>;
      function queryOrderStatus() {
        $.ajax({
          type: 'get',
          url: 'https://ooopay.in/order/wechat/check.php',
          data: {
            name: order_id,
            method: 'query'
          },
          timeout: 6000,
          cache: false,
          dataType: 'json',
          async: true,
          success: function(e) {
            console.log(e);
            // 支付超时，订单已过期
            if (e.code == 400000) {
              if (confirm("支付超时, 订单已过期")) {
                location.href = <?php echo json_encode($returnURl); ?>;
              }
            }
            // 已支付
            else if (e.code == 200) {
              if (confirm("订单已支付")) {
                location.href = <?php echo json_encode($returnURl); ?>;
                return;
              }
            }
            console.log(e);
            setTimeout(queryOrderStatus, 2000);
          },
          error: function(e) {
            setTimeout(queryOrderStatus, 2000);
          }
        });
      }
      queryOrderStatus();
    })(jQuery);

    // Response
    var isMobileDevice = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    if (isMobileDevice) {
      $('#mobile-tips').removeClass('uk-hidden')
    }
  </script>
  <script type="text/javascript">
    var qrc = '<? echo $qrcStr; ?>';
    if (qrc) {
      qrcode('qrcode', 200, 200, '<? echo $qrcStr; ?>')
    } else {
      $('.uk-cover-container').empty().html(`<p class="uk-placeholder uk-width-1-1 uk-text-primary uk-text-center">该支付通道暂不可用，请返回取消订单重新尝试下单，或联系客服处理。</p>`)
    }
  </script>
</body>

</html>