<?php

include_once('../base.php');

$sign = $_GET['sign'] ? $_GET['sign'] : '';

$params = [
  'money' => $_GET['money'] ? $_GET['money'] : '',
  'name' => $_GET['name'] ? $_GET['name'] : '',
  'notify_url' => $_GET['notify_url'] ? $_GET['notify_url'] : '',
  'return_url' => $_GET['return_url'] ? $_GET['return_url'] : '',
  'out_trade_no' => $_GET['out_trade_no'] ? $_GET['out_trade_no'] : '',
  'pid' => $_GET['pid'] ? $_GET['pid'] : ''
];

$OOPay = new OOPay();

// 缺少参数
if (!$params['money'] || !$sign) die('<hr>404|ERROR');
// 签名验证
if ($sign != $OOPay->sign($params)) {
  die('签名验证失败，请求参数已被篡改');
}

$order = json_decode($OOPay->checkOrder($params['name']));

//不存在
if ($order->code == 300000) {

  // 随机选择支付
  /*
  lty: 33
  lem: 34
  */
  $payments = array(33, 34);
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

$qrcode = 'https://qrc.ooopay.in/qrcode/' . $params['name'] . '.png';
$returnURl = 'https://catcloud.in/#/order';
?>

<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="keywords" content="">
  <meta name="description" content="">
  <title>支付宝收银台</title>
  <style>
    * {
      margin: 0;
      padding: 0;
    }

    body {
      background: #f2f2f4;
    }

    .clearfix:after {
      content: ".";
      display: block;
      height: 0;
      clear: both;
      visibility: hidden;
    }

    .clearfix {
      display: inline-block;
    }

    * html .clearfix {
      height: 1%;
    }

    .clearfix {
      display: block;
    }

    .xh-title {
      height: 75px;
      line-height: 75px;
      text-align: center;
      font-size: 30px;
      font-weight: 300;
      border-bottom: 2px solid #eee;
      background: #fff;
    }

    .qrbox {
      max-width: 900px;
      margin: 0 auto;
      padding: 85px 20px 20px 50px;
    }

    .qrbox .left {
      width: 40%;
      float: left;
      display: block;
      margin: 0px auto;
    }

    .qrbox .left .qrcon {
      border-radius: 10px;
      background: #fff;
      overflow: visible;
      text-align: center;
      padding-top: 25px;
      color: #555;
      box-shadow: 0 3px 3px 0 rgba(0, 0, 0, .05);
      vertical-align: top;
      -webkit-transition: all .2s linear;
      transition: all .2s linear;
    }

    .qrbox .left .qrcon .logo {
      width: 100%;
    }

    .qrbox .left .qrcon .title {
      font-size: 16px;
      margin: 10px auto;
      width: 90%;
    }

    .qrbox .left .qrcon .price {
      font-size: 22px;
      margin: 0px auto;
      width: 100%;
    }

    .qrbox .left .qrcon .bottom {
      border-radius: 0 0 10px 10px;
      width: 100%;
      background: #32343d;
      color: #f2f2f2;
      padding: 15px 0px;
      text-align: center;
      font-size: 14px;
    }

    .qrbox .sys {
      width: 60%;
      float: right;
      text-align: center;
      padding-top: 20px;
      font-size: 12px;
      color: #ccc
    }

    .qrbox img {
      max-width: 100%;
    }

    @media (max-width : 767px) {
      .qrbox {
        padding: 20px;
      }

      .qrbox .left {
        width: 90%;
        float: none;
      }

      .qrbox .sys {
        display: none;
      }
    }
  </style>
  <!--[if IE]>
  <script src="/content/js/html5shiv.js"></script>
  <![endif]-->
</head>

<body data-new-gr-c-s-check-loaded="14.1063.0" data-gr-ext-installed="">
  <div class="xh-title">支付宝收银台</div>
  <div class="qrbox clearfix">
    <div class="left">
      <div class="qrcon">
        <h5><img src="/order/alipay/alipay_files/logo.png" alt="" style="height:30px;"></h5>
        <div class="title">订单号: <?php echo $_GET['name']; ?></div>
        <div style="margin-bottom: 10px;" class="price">￥<?php echo $_GET['money']; ?></div>
        <div id="qrcodeContainer" align="center" style="position:relative;">
          <div id="qrcode" style="margin-bottom: 10px;"></div>
        </div>
        <p style="margin-bottom: 10px;"><a id="jump_payuri" href="#">点击打开支付宝APP</a></p>
        <div class="bottom">
          请使用支付宝APP扫一扫<br>扫描二维码支付
        </div>
      </div>
    </div>
    <div class="sys"><img src="/order/alipay/alipay_files/alipay-sys.png" alt=""></div>
  </div>
  <script type="text/javascript" src="/order/alipay/alipay_files/jquery-2.1.4.js"></script>
  <script src="https://cdn.bootcdn.net/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
  <script type="text/javascript">
    (function($) {
      var order_id = <?php echo json_encode($_GET['name']); ?>;
      var qrcodeIsGenerator = false

      function queryOrderStatus() {
        $.ajax({
          type: 'get',
          url: 'https://ooopay.in/order/alipay2/check.php',
          data: {
            name: order_id,
            method: 'query'
          },
          timeout: 6000,
          cache: false,
          dataType: 'json',
          async: true,
          success: function(e) {
            if (!qrcodeIsGenerator) {
              $('#qrcode').qrcode({
                width: 230,
                height: 230,
                text: e.payment.jump_payuri
              })
              qrcodeIsGenerator = true
            }
            $('#jump_payuri').attr('href', e.payment.jump_payuri)

            var isMobileDevice = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

            if (isMobileDevice) {
              setTimeout(function() {
                $(location).attr('href', e.payment.jump_payuri);
              }, 1000)
              // window.location.replace(e.payment.jump_payuri);
              // window.location.href(e.payment.jump_payuri);
            } else {
              $('#jump_payuri').remove()
            }

            // 支付超时，订单已过期
            if (e.order.status == -1) {
              if (confirm("支付超时, 订单已过期")) {
                location.href = <?php echo json_encode($returnURl); ?>;
              }
            }
            // 已支付
            else if (e.order.status > 1) {
              if (confirm("订单已支付")) {
                location.href = <?php echo json_encode($returnURl); ?>;
                return;
              }
            }
            // console.log(e);
            setTimeout(queryOrderStatus, 2000);
          },
          error: function(e) {
            setTimeout(queryOrderStatus, 2000);
          }
        });
      }
      queryOrderStatus();
    })(jQuery);
  </script>
</body>

</html>