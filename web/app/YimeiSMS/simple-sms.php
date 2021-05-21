<?php
date_default_timezone_set('PRC');
/* 非加密接口demo */
define("YM_SMS_ADDR",           		"100.100.11.68:8999");/*接口地址,请联系销售获取*/
define("YM_SMS_SEND_URI",       		"/simpleinter/sendSMS");/*发送短信接口*/
define("YM_SMS_SEND_PER_URI",       	"/simpleinter/sendPersonalitySMS");/*发送个性短信接口*/
define("YM_SMS_GETREPORT_URI",  		"/simpleinter/getReport");/*获取状态报告接口*/
define("YM_SMS_GETMO_URI",      		"/simpleinter/getMo");/*获取上行接口*/
define("YM_SMS_GETBALANCE_URI", 		"/simpleinter/getBalance");  /*获取余额接口*/
define("YM_SMS_APPID",          		"3MV5-EMY-0130-OFTSO");/*APPID,请联系销售或者在页面获取*/
define("YM_SMS_AESPWD",         		"6ED2843F2541E3E4");/*密钥，请联系销售或者在页面获取*/

define("END",               "\n");
    
function http_request($url, $data)
{
    print_r($url);
    print_r(END);
    print_r($data);
    print_r(END);
    $data = http_build_query($data);	
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $output = curl_exec($curl);
    curl_close($curl);
    print_r($output);
    return $output;
}

function signmd5($appId,$secretKey,$timestamp){
	return md5($appId.$secretKey.$timestamp);
}

function sendSMS()
{   
    $content = "【某某公司】您的验证码是123&st=xxx";/* 短信内容请以商务约定的为准，如果已经在通道端绑定了签名，则无需在这里添加签名 */
    $timestamp = date("YmdHis");
    $sign = signmd5(YM_SMS_APPID,YM_SMS_AESPWD,$timestamp);
    // 如果您的系统环境不是UTF-8，需要转码到UTF-8。如下：从gb2312转到了UTF-8
    // $content = mb_convert_encoding( $content,"UTF-8","gb2312");
    // 另外，如果包含特殊字符，需要对内容进行urlencode
    $data = array(
        "appId" => YM_SMS_APPID,
        "timestamp" => $timestamp,
        "sign" => $sign,
        "mobiles" => "18001000000,18001000001",
        "content" =>  $content,
        "customSmsId" => "10001",
        "timerTime" => "20170910110200",
        "extendedCode" => "1234"
    );
    $url = YM_SMS_ADDR.YM_SMS_SEND_URI;
    $resobj = http_request($url, $data);
}   

function setPersonalitySms()
{   
	$mobile1 = "18001000000";
    $content1 = "今天天气不错啊&st=xxx";
    $mobile2 = "18001000001";
    $content2 = "今天天气不错";
    $timestamp = date("YmdHis");
    $sign = signmd5(YM_SMS_APPID,YM_SMS_AESPWD,$timestamp);
    // 如果您的系统环境不是UTF-8，需要转码到UTF-8。如下：从gb2312转到了UTF-8
    // $content = mb_convert_encoding( $content,"UTF-8","gb2312");
    // 另外，如果包含特殊字符，需要对内容进行urlencode
    $data = array(
        "appId" => YM_SMS_APPID,
        "timestamp" => $timestamp,
        "sign" => $sign,
        $mobile1 => $content1,
        $mobile2 => $content2,
        "customSmsId" => "10001",
        "timerTime" => "20170910110200",
        "extendedCode" => "1234"
    );
    $url = YM_SMS_ADDR.YM_SMS_SEND_PER_URI;
    $resobj = http_request($url, $data);
}   

function getReport()
{   
	$timestamp = date("YmdHis");
	$sign = signmd5(YM_SMS_APPID,YM_SMS_AESPWD,$timestamp);
    $data = array(
        "appId" => YM_SMS_APPID,
        "timestamp" => $timestamp,
        "sign" => $sign,
        "number" => "300"
    );
    $url = YM_SMS_ADDR.YM_SMS_GETREPORT_URI;
    $resobj = http_request($url, $data);
}  

function getMo()
{   
	$timestamp = date("YmdHis");
	$sign = signmd5(YM_SMS_APPID,YM_SMS_AESPWD,$timestamp);
    $data = array(
        "appId" => YM_SMS_APPID,
        "timestamp" => $timestamp,
        "sign" => $sign,
        "number" => "300"
    );
    $url = YM_SMS_ADDR.YM_SMS_GETMO_URI;
    $resobj = http_request($url, $data);
}  

function getBalance()
{   
	$timestamp = date("YmdHis");
	$sign = signmd5(YM_SMS_APPID,YM_SMS_AESPWD,$timestamp);
    $data = array(
        "appId" => YM_SMS_APPID,
        "timestamp" => $timestamp,
        "sign" => $sign
    );
    $url = YM_SMS_ADDR.YM_SMS_GETBALANCE_URI;
    $resobj = http_request($url,$data);
}  
    
function run(){
    echo "***************测试短信发送START***************".END;
    SendSMS();
    echo END;
    echo "***************测试短信发送END***************".END;

    echo "***************测试个性短信发送START***************".END;
    setPersonalitySms();
    echo END;
    echo "***************测试个性短信发送END***************".END;

    echo "***************测试获取余额START***************".END;
    getBalance();
    echo END;
    echo "***************测试获取余额END***************".END;

    echo "***************测试获取状态报告START***************".END;
    getReport();
    echo END;
    echo "***************测试获取状态报告END***************".END;

    echo "***************测试获取上行START***************".END;
    getMo();
    echo END;
    echo "***************测试获取上行END***************".END;

}
    
run();

?>
