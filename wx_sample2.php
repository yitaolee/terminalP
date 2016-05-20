<?php
require_once("./M/fanta7_fns.php");
db_connect();
/**
  * wechat php test
  */

//define your token
 //require_once ('Weixin.php');
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
if($_GET["echostr"]!=null)
{
    $wechatObj->valid();
}
$wechatObj->getOpenId();
$resultUser = mysql_query("select * from users where passwd='".$wechatObj->getOpenId()."'");
if($resultUser)
{
    if(mysql_num_rows($resultUser)==0){
        $resultUserInsert = mysql_query("insert into users values('','','".$wechatObj->getOpenId()."','".$wechatObj->getUnionId()."','0',now())");
    }
}
$wechatObj->getUserMsg($wechatObj->getOpenId());

class wechatCallbackapiTest
{
    private $from;
    private $to;
    private $time;
    private $content;

    public function getUserMsg($iduser){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (!empty($postStr)){
            
            $this->from = $postObj->FromUserName;
            $this->to = $postObj->ToUserName;
            $this->time = $postObj->CreateTime;
            
            $resultwid = mysql_query("select Aid from Wechat where wechatid='".$this->to."'");
            $Aid=mysql_fetch_array($resultwid);

            if($postObj->MsgType=="event"&&$postObj->Event=="subscribe"){
                //这里写关注时的东西
                $resultwid = mysql_query("select Aid from Wechat where wechatid='".$this->to."'");
                $Aid=mysql_fetch_array($resultwid);
                $resultResp = mysql_query("select data, type from WechatMsg where MsgFrom='关注回复' and Wid='".$Aid['Aid']."'");
                $addAndResp=mysql_fetch_array($resultResp);
                if($addAndResp['type']==0){
                $this->responseText($addAndResp['data']);
                }
                else if($addAndResp['type']==1)
                {
                 //tu wen
                    $output=sprintf($addAndResp['data'],$this->from,$Aid['Aid']);
                    $count=1;
                    while($respMsg=mysql_fetch_array($resultResp))
                    {
                        $resultStr = sprintf($respMsg['data'],$this->from,$Aid['Aid']);
                        $output=$output.$resultStr;
                        $count++;
                    }
                    $this->responseNews($count,$output);


                }                

            }else if ($postObj->MsgType=="text"||($postObj->MsgType=="event" && $postObj->Event=="CLICK")) { 
                if ($postObj->MsgType=="event" && $postObj->Event=="CLICK") {
                    $content = trim($postObj->EventKey);
                }else{
                    $content = trim($postObj->Content);
                }  
                
                //关键字回复
                if($Aid['Aid']!=null){
                    $filename = "./M/newIndex/admin/wechat/json/data".$Aid['Aid'].".json";
                    $handle = fopen($filename, "r");
                    $contents = fread($handle, filesize($filename));
                    fclose($handle);
                    $ss= json_decode($contents,true);

                    $resultResp = mysql_query("select * from WechatMsg where MsgFrom='".$content."' and Wid='".$Aid['Aid']."'");
                    $addAndResp=mysql_fetch_array($resultResp);
                        if($addAndResp['type']==0){
                        $this->responseText($ss[$addAndResp['MsgFrom']]['text']);
                        }
                        else if($addAndResp['type']==1)
                        {
                         //tu wen
                            $output='';
                            $count=0;
                            while($count<sizeof($ss[$addAndResp['MsgFrom']]))
                            {
                                $textAD = "<item><Title><![CDATA[".$ss[$addAndResp['MsgFrom']][$count]['Title']."]]></Title> 
                                <Description><![CDATA[".$ss[$addAndResp['MsgFrom']][$count]['Description']."]]></Description>
                                <PicUrl><![CDATA[".$ss[$addAndResp['MsgFrom']][$count]['PicUrl']."]]></PicUrl>
                                <Url><![CDATA[".$ss[$addAndResp['MsgFrom']][$count]['Url']."]]></Url>
                                </item>";
                                $output.=$textAD;
                            $count++;
                            }              
                            $this->responseNews($count,$output);


                        }
                        else if($addAndResp['type']==2)
                        {//ad


                            $output='';
                            $count=0;
                            while($count<sizeof($ss[$addAndResp['MsgFrom']]))
                            {
                                $textAD = "<item><Title><![CDATA[".$ss[$addAndResp['MsgFrom']][$count]['Title']."]]></Title> 
                                <Description><![CDATA[".$ss[$addAndResp['MsgFrom']][$count]['Description']."]]></Description>
                                <PicUrl><![CDATA[".$ss[$addAndResp['MsgFrom']][$count]['PicUrl']."]]></PicUrl>
                                <Url><![CDATA[".$ss[$addAndResp['MsgFrom']][$count]['Url']."]]></Url>
                                </item>";
                                $output.=$textAD;
                            $count++;
                            }              

                            $this->responseNews($count,$output);
                            
                        }
                        $arrayT=explode(" ",$urlback);
                        for($i=0;$i<sizeof($arrayT)-1;$i++)
                        {
                            $resultT = mysql_query("insert into adShowTimes values('','".$arrayT[$i]."',now())");
                        }
                        $resultKey = mysql_query("insert into keywordsRecord values('','".$content."','".$this->from."','".$this->to."',now())");
                }
                else
                {
                    $this->responseText("您的微信公众账号id为:".$this->to." 请输入zx-mobi.com后台系统。");
                }
            }
        }
    }
        
    

    private function responseText($content){

            $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";            
            $resultStr = sprintf($textTpl, $this->from, $this->to, time(), "text", $content);

            
            echo $resultStr;
    }
    private function responseNews($num,$articles){

            

            $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <ArticleCount>%d</ArticleCount>
                            <Articles>
                                %s
                            </Articles>

                            </xml>";
            $resultStr = sprintf($textTpl, $this->from, $this->to, time(), "news", $num,$articles);
            echo $resultStr;
    }
    public function getOpenId(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!empty($postStr)){
            
            $this->from = $postObj->FromUserName;
        }
        return $this->from;
    }
    public function getUnionId(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!empty($postStr)){
            
            $this->to = $postObj->ToUserName;
        }
        return $this->to;
    }





    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
         echo $echoStr;
         exit;
        }
    }

    public function responseMsg()
    {
     //get post data, May be due to the different environments
     $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

         //extract post data
     if (!empty($postStr)){
                
                 $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
                         <ToUserName><![CDATA[%s]]></ToUserName>
                         <FromUserName><![CDATA[%s]]></FromUserName>
                         <CreateTime>%s</CreateTime>
                         <MsgType><![CDATA[%s]]></MsgType>
                         <Content><![CDATA[%s]]></Content>
                         <FuncFlag>0</FuncFlag>
                         </xml>";             
             if(!empty( $keyword ))
                {
                     $msgType = "text";
                 $contentStr = "Welcome to wechat world!";
                 $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                 echo $resultStr;
                }else{
                 echo "Input something...";
                }

        }else {
         echo "";
         exit;
        }
    }
        
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"]; 
                
     $token = TOKEN;
     $tmpArr = array($token, $timestamp, $nonce);
     sort($tmpArr);
     $tmpStr = implode( $tmpArr );
     $tmpStr = sha1( $tmpStr );
        
     if( $tmpStr == $signature ){
         return true;
     }else{
         return false;
     }
    }
}
?>