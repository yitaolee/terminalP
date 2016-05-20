<?php

require_once("../fanta7_fns.php");
header("Content-type: text/html; charset=utf-8");

function createMenu($accessToken,$data){
	$post_string = "access_token=".$accessToken;
	request_by_other('https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$accessToken,$post_string);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$accessToken);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$tmpInfo = curl_exec($ch);
	if (curl_errno($ch)) {
		return curl_error($ch);
	}
	curl_close($ch);
	return $tmpInfo;
}


db_connect();
$appid=$_POST['appid'];
$secret=$_POST['secret'];
$url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
$html = file_get_contents($url);
$de_json = json_decode($html);
if ($de_json->access_token!=null) {
	$accessToken=$de_json->access_token;
}

$menu='';
for ($i=1; $i <4 ; $i++) {
	$name=$POST['button'.$i.'00'];
	if ($name！=null) {
		for ($j=1; $j <6 ; $j++) {
			$subName= $POST['button'.$i.$j.'1'];
			$subUrl=$POST['button'.$i.$j.'2'];
			$subMenu.='{"type":"view","name":"'.$subName.'","url":"'.$subUrl.'"}';
		}
	 	$menu.='{"name":"'.$name.'","sub_button":['.$subMenu.']}';
	 } 
	
}


$data = ' {
"button":[
	'.$menu.'
]
}';


createMenu($accessToken,$data);//创建菜单
?>

