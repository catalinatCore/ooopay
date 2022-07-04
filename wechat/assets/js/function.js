//生成二维码
function qrcode (dom, width, height, url) {
  $('#' + dom).qrcode({
    width: width,
    height: height,
    text: url
  });
}