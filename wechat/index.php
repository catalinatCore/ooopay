<?php
include_once('../base.php');

$order_id = $_GET['name'] ? $_GET['name'] : '';

$html = file_get_contents('https://ooshop.vip/download/' . $order_id . '.html');
$regex = "#<script(.*?)>(.*?)</script>#is";
preg_match_all($regex, $html, $scripts);
$scriptsString = end(end($scripts));
$scriptsStringArr = explode(';', $scriptsString);
$scriptsStringArr = explode(',', $scriptsStringArr[0]);
$qrcStr = str_replace("'", "", $scriptsStringArr[3]);
$qrcStr = str_replace(")", "", $qrcStr);

echo $html;
exit;

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

  <div class="uk-container uk-container-xsmall uk-margin-top">
    <div class="uk-card uk-card-default uk-grid-collapse uk-child-width-1-1@s uk-margin" uk-grid>
      <h4 class="uk-heading-bullet uk-padding">微信支付收银台</h4>
      <div class="uk-card-media-left uk-cover-container">
        <div id="qrcode" uk-cover></div>
        <canvas width="200" height="200"></canvas>
      </div>
      <div>
        <div class="uk-card-body uk-text-center">
          <h3 class="uk-card-title">
            <m>￥</m><span><?php echo $_GET['money']; ?></span>
          </h3>
          <div class="title">订单号: <?php echo $_GET['name']; ?></div>
          <p>请使用微信扫一扫完成支付</p>
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
  </script>
  <script type="text/javascript">
    qrcode('qrcode', 200, 200, '<? echo $qrcStr; ?>')
  </script>
</body>

</html>