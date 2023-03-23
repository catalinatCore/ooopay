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

  <style>
    /* CSS */
    .button-24 {
      background: #03A9F4;
      border: 1px solid #03A9F4;
      border-radius: 6px;
      box-shadow: rgba(0, 0, 0, 0.1) 1px 2px 4px;
      box-sizing: border-box;
      color: #FFFFFF;
      cursor: pointer;
      display: inline-block;
      font-family: nunito, roboto, proxima-nova, "proxima nova", sans-serif;
      font-size: 14px;
      line-height: 16px;
      min-height: 40px;
      outline: 0;
      padding: 10px 12px;
      text-align: center;
      text-rendering: geometricprecision;
      text-transform: none;
      user-select: none;
      -webkit-user-select: none;
      touch-action: manipulation;
      vertical-align: middle;
      text-decoration: none;
    }

    /* .button-24:hover,
    .button-24:active {
      background-color: initial;
      background-position: 0 0;
      color: #FF4742;
      border: 1px solid #F44336;
      text-underline-offset: unset;
    } */

    .button-24:active {
      opacity: .5;
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
        <p style="margin-bottom: 10px;">
          <a class="button-24" style="display: none;" role="button" id="jump_payuri" href="#">
            <svg t="1679522277988" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="8250" width="15" height="15">
              <path d="M308.6 545.7c-19.8 2-57.1 10.7-77.4 28.6-61 53-24.5 150 99 150 71.8 0 143.5-45.7 199.8-119-80.2-38.9-148.1-66.8-221.4-59.6z" p-id="8251" fill="#ffffff"></path>
              <path d="M769.1 612.7c100.1 33.4 154.7 43 166.7 44.8C951.5 611.9 960 563 960 512c0-247.4-200.6-448-448-448S64 264.6 64 512s200.6 448 448 448c155.9 0 293.2-79.7 373.5-200.5-75.6-29.8-213.6-85-286.8-120.1-69.9 85.7-160.1 137.8-253.7 137.8-158.4 0-212.1-138.1-137.2-229 16.3-19.8 44.2-38.7 87.3-49.4 67.5-16.5 175 10.3 275.7 43.4 18.1-33.3 33.4-69.9 44.7-108.9H305.1V402h160v-56.2H271.3v-31.3h193.8v-80.1s0-13.5 13.7-13.5H557v93.6h191.7v31.3H557.1V402h156.4c-15 61.1-37.7 117.4-66.2 166.8 47.5 17.1 90.1 33.3 121.8 43.9z" p-id="8252" fill="#ffffff"></path>
            </svg> 点击打开支付宝</a>
        </p>
        <p style="margin-bottom: 10px;">
          <button id="changePaymentButton" class="button-24" role="button"><svg t="1679522217479" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="7220" width="15" height="15">
              <path d="M512.2 104.2C286.8 104.2 104 287 104 512.4s182.7 408.2 408.2 408.2 408.2-182.7 408.2-408.2-182.8-408.2-408.2-408.2z m196.1 520.2c-15 12.6-33.3 22.5-49.5 33.5-29.9 20.2-59.8 40.5-89.7 60.7-7.6 5.1-15.1 10.9-23 15.5-17 10.1-41.3-2.2-33.9-24 4.6-13.5 25.8-22.8 36.6-30.1 25.3-17.1 50.7-34.3 76-51.4h-37.5c-86.9 0-173.8 0.5-260.6 0-28.8-0.5-26.3-41.4 1-43.3 6.3-0.5 12.8 0 19.1 0H654c13.6 0 27.9-1.2 41.5 0 20.7 1.8 29.2 25.3 12.8 39.1z m-12.9-183.2c-6.3 0.5-12.8 0-19.1 0H369.1c-13.6 0-27.9 1.2-41.5 0-20.7-1.8-29.2-25.3-12.8-39.2 15-12.6 33.3-22.5 49.5-33.5 29.9-20.2 59.8-40.5 89.7-60.7 7.6-5.1 15.1-10.9 23-15.5 17-10.1 41.3 2.2 33.9 24-4.6 13.5-25.8 22.8-36.6 30.1-25.3 17.1-50.7 34.3-76 51.4h37.5c86.9 0 173.8-0.5 260.6 0 28.8 0.6 26.3 41.4-1 43.4z" fill="#ffffff" p-id="7221"></path>
            </svg>点击切换备用支付</button>
        </p>
        <p id="changePaymentTips" style="margin-bottom: 10px;padding: 1px;font-size: smaller;color: red;">如遇到无法支付，请点击以上按钮切换备用支付</p>
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
      var price = <?php echo json_encode($_GET['money']); ?>;
      var qrcodeIsGenerator = false
      var isMobileDevice = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

      // Timers
      queryOrderTimer = setInterval(queryOrderStatus, 3000)
      queryBackupOrderTimer = setInterval(queryBackupOrderStatus, 4000)

      function canvasToImage(canvas) {
        var image = new Image();
        image.src = canvas.toDataURL("image/png");
        return image;
      }

      function queryOrderStatus() {
        $.ajax({
          type: 'get',
          url: '/order/alipay2/check.php',
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
              qrcodeIsGenerator = true

              $('#qrcode').qrcode({
                width: 230,
                height: 230,
                text: e.payment.jump_payuri
              })
              var getCanvas = document.getElementsByTagName('canvas')[0];
              var qrcImage = canvasToImage(getCanvas);
              $('#qrcode').empty().append(qrcImage)
            }

            $('#jump_payuri').show().attr('href', e.payment.jump_payuri)

            if (isMobileDevice) {
              // setTimeout(function() {
              //   $(location).attr('href', e.payment.jump_payuri);
              // }, 1000)
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
          },
          error: function(e) {
            clearInterval(queryOrderTimer)
          }
        });
      }
      function queryBackupOrderStatus() {
        $.ajax({
          type: 'get',
          url: '/order/alipay2/check.php',
          data: {
            name: order_id,
            method: 'queryBackupOrderStatus'
          },
          timeout: 6000,
          cache: false,
          dataType: 'json',
          async: true,
          success: function(e) {
            console.log(e)
            if (e.error || !e.data) {
              $('#qrcode').empty().append(`订单超时已取消，请反回重新下单`)
              return
            }
            // 支付超时，订单已过期
            if (e.data.status == 2) {
              if (confirm("支付超时, 订单已过期或已取消")) {
                location.href = <?php echo json_encode($returnURl); ?>;
              }
            }
            // 已支付
            else if (e.data.status == 3 || e.data.callback_no) {
              if (confirm("订单已支付")) {
                location.href = <?php echo json_encode($returnURl); ?>;
                return;
              }
            }
          },
          error: function(e) {
            clearInterval(queryBackupOrderTimer)
          }
        });
      }

      function changePayment() {
        $('#changePaymentButton').html('备用支持切换中...').attr("disabled", true);
        $.ajax({
          type: 'get',
          url: '/order/alipay2/changePayment.php',
          data: {
            order_id: order_id,
            price: price,
            method: 'query'
          },
          timeout: 6000,
          cache: false,
          dataType: 'json',
          async: true,
          success: function(e) {
            console.log(e)
            if (e.error || !e.data) {
              $('#qrcode').empty().append(`订单超时已取消，请反回重新下单`)
              return
            }

            $('#qrcode')
              .empty()
              .css({
                padding: '10px'
              })
              .append(`
                <p style="color: red;">支付已切换，请点击以下链接进行支付</p><br/>
                <a target="_blank" style="word-break: break-all;font-size: small;padding: 10px;" href='${e.data}'>${e.data}</a>
              `)

            $('#changePaymentButton').hide()
            $('#changePaymentTips').hide()
            $('#jump_payuri').hide()

            // timer
            clearTimeout(queryOrderTimer)
          },
          error: function(e) {
            console.log(e)
          }
        });
      }

      $("#changePaymentButton").click(function() {
        changePayment()
      })

      queryOrderStatus();
    })(jQuery);
  </script>
</body>

</html>