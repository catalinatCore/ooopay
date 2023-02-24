
//生成二维码
function zhanpay_qrcode(dom,width,height,url){
$('#'+dom).qrcode({
width:width,
height:height,
text:url
});
}


//打开微信开户表单
function zhanpay_wechat_mch_open_form(){
// layer.load(1);
// $.ajax({
// type:"POST",
// url:zhanpay.ajax_url+"/action/is_follow_wechat_mp.php",
// data:{user_id:zhanpay.user_id},
// success: function(msg){

// if(msg.code==1||msg.code==2){//已经关注或无法链接
layer.load(1);
$.ajax({
type:"POST",
url:zhanpay.ajax_url+"/stencil/mch-open.php",
success: function(msg){
layer.closeAll('loading');
layer.open({
type: 1,
title: '微信平台开户资料填写',
area: ['700px', '535px'],
content:msg
});
}
});

// }else{
// layer.closeAll('loading');
// layer.open({
// type: 1,
// title: '关注公众号',
// area: ['300px', '300px'],
// content:'<div class="zhanpay-panel-mch-pay-qrcode follow-mp"><img src="'+zhanpay.theme_url+'/assets/img/wechat-mp-code.jpg"><p>请先扫码关注公众号</p></div>'
// });
// }

// }
// });	


	
}


//查询开户状态
function zhanpay_check_mch(out_request_no,obj){
layer.load(1);
$.ajax({
type:"POST",
url:zhanpay.ajax_url+"/action/mch-status-check.php",
data:{out_request_no:out_request_no},
success: function(msg){
layer.closeAll('loading');

if(msg.sub_mchid){//如果已经签发了商户号则显示
$(obj).parent().siblings('.mch').text(msg.sub_mchid);
}

if(msg.status=='FINISH'){//完成
layer.msg('订单已完成！');
$(obj).parent().html('<k style="color:#4caf50;">完成</k>');
$(obj).parent().siblings('.do').html('<m>管理</m>');
}else if(msg.status=='PAY'){//开户状态
zhanpay_open_mch_select(msg.sub_mchid);//弹出开户套餐选择
$(obj).parent().html('<k>待开户</k><m onclick="zhanpay_open_mch_select('+msg.sub_mchid+')" style="background:#4caf50;">马上开户</m>');
}else if(msg.status=='CHECKING'||msg.status=='AUDITING'){//等待审核
layer.msg('微信平台正在审核中('+msg.status+')');
}else if(msg.status=='NEED_SIGN'){//等待签约
zhanpay_mch_sign_code(out_request_no,msg.sign_url);//展示签约二维码
$(obj).parent().html('<k>待签约</k><m onclick="zhanpay_check_mch('+msg.out_request_no+',this)">马上签约</m>');
}else{//已驳回||已冻结
if(msg.status=='REJECTED'){//已驳回
$(obj).text('查看原因').prev().html('<font style="color:#f00;">已驳回</font>');
$(obj).parent().siblings('.do').html('<m style="color:#f00;" onclick="zhanpay_del_mch('+msg.out_request_no+',this)">删除</m>');
}
layer.open({
type: 1,
title: '申请状态',
area: ['400px', '300px'],
content:msg.html
});
}

}
});	
}

//打开签约二维码
function zhanpay_mch_sign_code(out_request_no,url){
layer.open({
type: 1,
title:'签约服务',
area: ['400px', '400px'],
content:'<div class="zhanpay-panel-mch-sign-qrcode"><div id="mch-sign-code"></div><p>请该实名的微信扫码完成签约</p></div>',
cancel: function(index,layero){ 
sign_check_ajax.abort();
}
});
zhanpay_qrcode('mch-sign-code',200,200,url);//生成二维码	
zhanpay_mch_sign_check(out_request_no);
}

//查询是否已经签约成功了
function zhanpay_mch_sign_check(out_request_no){
sign_check_ajax=$.ajax({
type: "POST",
url:zhanpay.ajax_url+"/action/mch-sign-check.php",
data:{out_request_no:out_request_no},
success: function(msg){
if(msg.code==0){
zhanpay_mch_sign_check(out_request_no);
}else if(msg.code==1){//已经付款
$('#mch-sign-code').html('<i class="zhanpay-icon zhanpay-chenggong"></i>');
$('.zhanpay-panel-mch-sign-qrcode p').html('<font style="color:#4caf50;">签约成功</font>');
$('#mchid_'+msg.sub_mchid+' .status').html('待开户<m onclick=\'zhanpay_open_mch_select("'+msg.sub_mchid+'")\' style="background:#4caf50;">马上开户</m>');
}else{
zhanpay_mch_sign_check(out_request_no);	
}
}
});	
}


//删除请求单
function zhanpay_del_mch(out_request_no,obj){
$(obj).parents('li').fadeTo("slow",0.06, function(){
$(this).slideUp(0.06, function() {
$(this).remove();
});
});
$.ajax({
type:"POST",
url:zhanpay.ajax_url+"/action/mch-del.php",
data:{out_request_no:out_request_no}
});
}

//开户套餐选择
function zhanpay_open_mch_select(sub_mchid){
layer.load(1);
$.ajax({
type:"POST",
url:zhanpay.ajax_url+"/stencil/mch-open-select.php",
data:{sub_mchid:sub_mchid},
success: function(msg){
layer.closeAll('loading');
layer.open({
type: 1,
title: '开户套餐选择',
area: ['850px', '420px'],
content:msg
});
}
});
}

//打开支付开户费二维码
function zhanpay_pay_mch(sub_mchid,index){
if(!sub_mchid){
layer.msg('商户号还没签发，暂时未能开户！');
return false;
}
layer.load(1);
$.ajax({
type:"POST",
url:zhanpay.ajax_url+"/pay/pc.php",
data:{sub_mchid:sub_mchid,index:index},
success: function(msg){
layer.closeAll('loading');
if(msg.code==1){
layer.open({
type: 1,
title: '支付开户费用',
area: ['300px', '330px'],
content:'<div class="zhanpay-panel-mch-pay-qrcode"><div id="mch-pay-code"></div><p><i class="zhanpay-icon zhanpay-weixinzhifu"></i> 微信扫码支付</p></div>',
cancel: function(index,layero){ 
order_check_ajax.abort();
},
});
zhanpay_qrcode('mch-pay-code',200,200,msg.code_url);
zhanpay_order_check('order_mch',msg.out_trade_no);
}else{
layer.msg(msg.msg);	
}
}
});
}


//查询开户是否已经付款
function zhanpay_order_check(table_name,out_trade_no){
order_check_ajax=$.ajax({
type: "POST",
url:zhanpay.ajax_url+"/action/order-check.php",
data:{out_trade_no:out_trade_no,table_name:table_name},
success: function(msg){
if(msg.code==0){
zhanpay_order_check(table_name,out_trade_no);
}else if(msg.code==1){//已经付款
$('#mch-pay-code').html('<i class="zhanpay-icon zhanpay-chenggong"></i>');
$('.zhanpay-panel-mch-pay-qrcode p').text('已支付成功');
function c(){window.location.reload();}setTimeout(c,2000);
}else{
zhanpay_order_check(table_name,out_trade_no);	
}
}
});	
}


//支付测试
function zhanpay_native_pay_test(sub_mchid,pay_type){
if(pay_type=='wechat'){
title='请选择你需要测试的微信支付通道';
}else{
title='请选择你需要测试的支付宝支付通道';
}
out_trade_no=new Date().getTime();
layer.msg(title, {
time:0,
closeBtn:2,
btn: ['Native','H5','JSAPI','聚合支付','小程序支付'],
btn1: function(index, layero){
layer.closeAll();
layer.load(1);
$.ajax({
type:"POST",
url:zhanpay.home_url+"/pay/WeChatNative.php",
data:{test:1,sub_mchid:sub_mchid,appid:zhanpay.appid},
success: function(msg){
layer.closeAll('loading');
if(msg.code==1){
layer.open({
type: 1,
title: '微信Native扫码测试 ('+sub_mchid+')',
area: ['300px', '350px'],
content:'<div class="zhanpay-panel-mch-pay-qrcode native"><div id="mch-pay-code-test"></div><p>该费用将直接到你的商户账户：'+sub_mchid+'</p></div>',
cancel: function(index,layero){ 
order_check_test_ajax.abort();
},
});
zhanpay_qrcode('mch-pay-code-test',200,200,msg.code_url);
zhanpay_order_check_test('order',msg.out_trade_no);
}else{
layer.msg(msg.msg);
}
}
});
},
btn2: function(index, layero){
layer.closeAll();
layer.open({
type: 1,
title: '微信H5支付测试 ('+sub_mchid+')',
area: ['300px', '300px'],
content:'<div class="zhanpay-panel-mch-pay-qrcode h5"><div id="mch-pay-code-test">https://pay.senhuo.cn/pay/demo/wechat-h5.php?appid='+zhanpay.appid+'&sub_mchid='+sub_mchid+'&out_trade_no='+out_trade_no+'</div><p>请复制以上链接用<font style="color:#f00;">手机端浏览器</font>打开<br>支付完成会自动显示在你的订单里面<br>该测试费用会自动到你的<br>商户帐号：'+sub_mchid+'</p></div>',
cancel: function(index,layero){ 
order_check_test_ajax.abort();
},
});
zhanpay_order_check_test('order',out_trade_no);
},
btn3: function(index, layero){
layer.closeAll();
layer.open({
type: 1,
title: '微信JSAPI支付测试 ('+sub_mchid+')',
area: ['300px', '300px'],
content:'<div class="zhanpay-panel-mch-pay-qrcode h5 jsapi"><div id="mch-pay-code-test">https://pay.senhuo.cn/pay/demo/wechat-jsapi.php?appid='+zhanpay.appid+'&sub_mchid='+sub_mchid+'&out_trade_no='+out_trade_no+'</div><p>请复制以上链接在<font style="color:#f00;">微信</font>内打开<br>支付完成会自动显示在你的订单里面<br>该测试费用会自动到你的<br>商户帐号：'+sub_mchid+'</p></div>',
cancel: function(index,layero){ 
order_check_test_ajax.abort();
},
});
zhanpay_order_check_test('order',out_trade_no);
},
btn4: function(index, layero){
layer.closeAll();
window.open('https://pay.senhuo.cn/pay/Pay.php?out_trade_no='+out_trade_no+'&appid='+zhanpay.appid+'&sub_mchid='+sub_mchid+'&sub_notify_url=https://pay.senhuo.cn&total=1&title=商户号['+sub_mchid+']的订单测试_站长支付平台&redirect_url=https://pay.senhuo.cn/panel&test=1');	
},
btn5: function(index, layero){
layer.closeAll();
layer.load(1);
$.ajax({
type:"POST",
url:zhanpay.home_url+"/pay/demo/miniprogram.php",
data:{test:1,sub_mchid:sub_mchid,appid:zhanpay.appid},
success: function(msg){
layer.closeAll('loading');
if(msg.code==1){
layer.open({
type: 1,
title: '微信小程序扫码测试 ('+sub_mchid+')',
area: ['300px', '350px'],
content:'<div class="zhanpay-panel-mch-pay-qrcode miniprogram">'+msg.img+'<p>该费用将直接到你的商户账户：'+sub_mchid+'</p></div>'
});
// zhanpay_qrcode('mch-pay-code-test',200,200,msg.code_url);
}else{
layer.msg(msg.msg);
}
}
});	
}
});

}


//查询测试订单是否已经付款
function zhanpay_order_check_test(table_name,out_trade_no){
order_check_test_ajax=$.ajax({
type: "POST",
url:zhanpay.ajax_url+"/action/order-check.php",
data:{out_trade_no:out_trade_no,table_name:table_name},
success: function(msg){
if(msg.code==0){
zhanpay_order_check_test(table_name,out_trade_no);
}else if(msg.code==1){//已经付款
$('#mch-pay-code-test').html('<i class="zhanpay-icon zhanpay-chenggong"></i>');
$('.zhanpay-panel-mch-pay-qrcode p').text('已支付成功');
// function c(){window.location.reload();}setTimeout(c,2000);
}else{
zhanpay_order_check_test(table_name,out_trade_no);	
}
}
});	
}

//聚合页面支付订单查询
function zhanpay_pay_order_check(table_name,out_trade_no,redirect_url){
$.ajax({
type: "POST",
url:zhanpay.ajax_url+"/action/order-check.php",
data:{out_trade_no:out_trade_no,table_name:table_name},
success: function(msg){
if(msg.code==0){
zhanpay_pay_order_check(table_name,out_trade_no,redirect_url);
}else if(msg.code==1){//已经付款
$('#qrcode').html('<i class="zhanpay-icon zhanpay-chenggong"></i>');
$('.zhanpay-order-content .left .title').remove();
$('.zhanpay-order-content .left .tips').text('已支付成功');
function c(){window.open(redirect_url,'_self');}setTimeout(c,2500);
}else{
zhanpay_pay_order_check(table_name,out_trade_no,redirect_url);	
}
}
});	
}


//订单查询
function zhanpay_order_search(){
title=$('.zhanpay-panel-order-search .list li.title input').val();
sub_mchid=$('.zhanpay-panel-order-search .list li.sub_mchid input').val();
out_trade_no=$('.zhanpay-panel-order-search .list li.out_trade_no input').val();
trade_no=$('.zhanpay-panel-order-search .list li.trade_no input').val();

layer.load(1);
$.ajax({
type:"POST",
url:zhanpay.ajax_url+"/action/order-search.php",
data:{title:title,sub_mchid:sub_mchid,out_trade_no:out_trade_no,trade_no:trade_no},
success: function(msg){
layer.closeAll('loading');
$('.zhanpay-panel-order-list .content').html(msg);
}
});

}

//重置查询
function zhanpay_order_reset(){
$('.zhanpay-panel-order-search .list li input').val('');
zhanpay_order_search();
}

//补单
function zhanpay_renotify(trade_no){
layer.confirm('你确定要对该订单进行补单操作吗？',{
btnAlign: 'c',
}, function(){
layer.load(1);
$.ajax({
type:"POST",
url:zhanpay.ajax_url+"/action/order-renotify.php",
data:{trade_no:trade_no},
success: function(msg){
layer.closeAll('loading');
layer.msg(msg.msg);
}
});
});
}


//保存设置选项
function zhanpay_setting_save(){
wechat_mchid=$('.zhanpay-panel-setting-box li.wechat_mchid select').val();
receive=$('.zhanpay-panel-setting-box li.receive textarea').val();
copyright=$('.zhanpay-panel-setting-box li.copyright textarea').val();

wechat_on='';
$(".zhanpay-panel-setting-box li .list input[type=checkbox]:checked").each(function(){
wechat_on+=$(this).val()+',';
});
wechat_on=wechat_on.substring(0, wechat_on.lastIndexOf(','));//移除最后一个逗号

layer.load(1);
$.ajax({
type:"POST",
url:zhanpay.ajax_url+"/action/setting.php",
data:{wechat_mchid:wechat_mchid,receive:receive,copyright:copyright,wechat_on:wechat_on},
success: function(msg){
layer.closeAll('loading');
layer.msg(msg.msg);
}
});
}


//退出帐号
function zhanpay_login_out(){
layer.confirm('你确定要退出帐号吗？',{
btnAlign: 'c',
}, function(){
window.open('/login','_self');
});	
}


//设置cookie
function SetCookie(name,value){
var Days = 30;//一个月
var exp = new Date();
exp.setTime(exp.getTime() + Days*24*60*60*1000);
document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}


//发起退款表单
function zhanpay_order_refunds_form(trade_no,total){
layer.open({
type: 1,
title: '发起退款',
area: ['400px', '400px'],
content:'<div class="zhanpay-panel-order-refunds-form">\
<div class="tips">*所有已经支付成功的订单都已扣除平台手续费<br>*你本次退款不会返还平台手续费，手续费差额将从你的商户号余额中扣除，如有疑问请联系客服</div>\
<p>退款金额：<input type="number" value="'+total+'"><span>元</span></p>\
<textarea placeholder="请填写退款原因"></textarea>\
<div class="btn opacity" onclick=\'zhanpay_order_refunds(this,"'+trade_no+'")\'>确定提交退款</div>\
</div>',
});
}

//发起退款操作
function zhanpay_order_refunds(obj,trade_no){
reason=$(obj).prev().val();
total=$(obj).siblings('p').children('input').val();

if(!total){
layer.msg('退款金额不能为空！');
return false;
}

if(!reason){
layer.msg('退款原因不能为空！');
return false;
}

layer.load(1);
$.ajax({
type:"POST",
url:zhanpay.ajax_url+"/action/refunds.php",
data:{reason:reason,trade_no:trade_no,total:total},
success: function(msg){
layer.closeAll('loading');
layer.msg(msg.msg);

}
});


}


//关闭头部提示
function zhanpay_close_header_tips(obj){
$(obj).parent().fadeTo("slow",0.06, function(){
$(this).slideUp(0.06, function() {
$(this).remove();
});
});	
}