<?php
// load dependencies via Composer
require __DIR__ . '/vendor/autoload.php';

use PHPHtmlParser\Dom;

$redirectUrl = getRedirectUrl($_POST['paymentUrl'])[1];
$url = $redirectUrl;
$dom = new Dom;
$dom->loadFromUrl($url);
$images = $dom->find('img');
$imagesInput = [];
foreach ($images as $c) {
  $str = $c->outerHtml();
  $imagesInput[] = $c->getAttribute('src');
}
$qrcUrl = $imagesInput[1];
$qrcName = getImageName($qrcUrl);

if ($qrcName && $qrcUrl) {
  echo json_encode(['qrc'=>saveQrcAndReturnUrl($qrcUrl, $qrcName), 'status'=> 0 ]);
} else {
  echo json_encode(['qrc'=>saveQrcAndReturnUrl($qrcUrl, $qrcName), 'status'=> 1]);
}

function getImageName($url) {
  $exString = explode('=', $url);
  return $exString[1];
}

function saveQrcAndReturnUrl($url, $name) {
  if (!$url || !$name) {
    return json_encode(['msg'=>'no name or url', 'status'=> 300 ]);
  }

  $url = $url;
  //Image path
  $img =  'images/'.$name.'.png';
  //Save image
  $ch = curl_init($url);
  $fp = fopen($img, 'wb');
  curl_setopt($ch, CURLOPT_FILE, $fp);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_exec($ch);
  curl_close($ch);
  fclose($fp);
  return 'https://qrc.hp.ooshop.vip/'.$img;
}

function getRedirectUrl ($url) {
  stream_context_set_default(array(
      'http' => [
          'method' => 'HEAD'
      ],
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
      ]
  ));
  $headers = get_headers($url, 1);
  if ($headers !== false && isset($headers['Location'])) {
      return $headers['Location'];
  }
  return false;
}

