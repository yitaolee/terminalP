<?php  
!extension_loaded('curl') && die('The curl extension is not loaded.');  
   
$discuz_url = 'http://mp.weixin.qq.com';//论坛地址  
$login_url = $discuz_url .'/cgi-bin/loginpage?t=wxm2-login&lang=zh_CN';//登录页地址  
$get_url = $discuz_url .'/cgi-bin/message?t=message/list&count=20&day=7&token=1184922284&lang=zh_CN'; //我的帖子  
   
$post_fields = array();  
//以下两项不需要修改  
$post_fields['loginfield'] = 'username';  
$post_fields['loginsubmit'] = 'true';  
//用户名和密码，必须填写  
$post_fields['username'] = 'contactus@fanta7.com';  
$post_fields['password'] = 'lytssg';  
$post_fields['refer'] = 'space.php?do=home';  
   
//获取表单FORMHASH  
$ch = curl_init($login_url);  
curl_setopt($ch, CURLOPT_HEADER, 0);  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
$contents = curl_exec($ch);  
curl_close($ch);  
preg_match('/<input\s*type="hidden"\s*name="formhash"\s*value="(.*?)"\s*\/>/i', $contents, $matches);  
if(!empty($matches)) {  
    $formhash = $matches[1];  
} else {  
    die('Not found the forumhash.');  
}  
 $post_fields['formhash']=$formhash;
//POST数据，获取COOKIE  
$cookie_file = dirname(__FILE__) . '/cookie.txt';  
//$cookie_file = tempnam('/tmp');  
$ch = curl_init($login_url);  
curl_setopt($ch, CURLOPT_HEADER, 0);  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_POST, 1);  
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);  
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);  
curl_exec($ch);  
curl_close($ch);  
   
//带着上面得到的COOKIE获取需要登录后才能查看的页面内容  
$ch = curl_init($get_url);  
curl_setopt($ch, CURLOPT_HEADER, 0);  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);  
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);  
$contents = curl_exec($ch);  
curl_close($ch);  
   
var_dump($contents);  
?>