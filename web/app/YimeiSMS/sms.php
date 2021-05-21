<?php
date_default_timezone_set('PRC');

define("YM_SMS_ADDR",           		"100.100.11.68:8999");/*接口地址,请联系销售获取*/
define("YM_SMS_SEND_URI",       		"/inter/sendSingleSMS");/*发送单条短信接口*/
define("YM_SMS_SEND_BATCH_URI", 		"/inter/sendBatchSMS");/*发送批次短信接口*/
define("YM_SMS_SEND_BATCHONLY_SMS_URI",         "/inter/sendBatchOnlySMS");/*发送批次[不支持自定义smsid]短信接口*/
define("YM_SMS_SEND_PERSONALITY_SMS_URI",       "/inter/sendPersonalitySMS");/*发送个性短信接口*/
define("YM_SMS_GETREPORT_URI",  		"/inter/getReport");/*获取状态报告接口*/
define("YM_SMS_GETMO_URI",      		"/inter/getMo");/*获取上行接口*/
define("YM_SMS_GETBALANCE_URI", 		"/inter/getBalance");  /*获取余额接口*/
define("YM_SMS_APPID",          		"3MV5-EMY-0130-OFTSO");/*APPID,请联系销售或者在页面获取*/
define("YM_SMS_AESPWD",         		"6ED2843F2541E3E4");/*密钥，请联系销售或者在页面获取*/
define("EN_GZIP",              			 true);/* 是否开启GZIP */

define("END", "\n");

class MagicCrypt {
    private $iv = "0102030405060708";//密钥偏移量IV，可自定义
    private $encryptKey = YM_SMS_AESPWD;

    //加密
    public function encrypt($encryptStr) {
        $localIV = $this->iv;
        $encryptKey = $this->encryptKey;
        
        if (true == EN_GZIP)   $encryptStr = gzencode($encryptStr);
 
        //Open module
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, $localIV);
 
        mcrypt_generic_init($module, $encryptKey, $localIV);
 
        //Padding
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $pad = $block - (strlen($encryptStr) % $block); //Compute how many characters need to pad
        $encryptStr .= str_repeat(chr($pad), $pad); // After pad, the str length must be equal to block or its integer multiples
 
        //encrypt
        $encrypted = mcrypt_generic($module, $encryptStr);
 
        //Close
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
 
        return $encrypted;
    }
 
    //解密
    public function decrypt($encryptStr) {
        $localIV = $this->iv;
        $encryptKey = $this->encryptKey;
 
        //Open module
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, $localIV);
 
        mcrypt_generic_init($module, $encryptKey, $localIV);
 
        $encryptedData = mdecrypt_generic($module, $encryptStr);
        
        if (true == EN_GZIP)   $encryptedData = gzdecode($encryptedData);
 
        return $encryptedData;
    }
}
    
function http_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, true);
    $header[] = "appId: ".YM_SMS_APPID;
    if (true == EN_GZIP)   $header[] = "gzip: on";
    print_r($header);echo END;
    
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    
    curl_setopt($curl, CURLOPT_HEADER, true);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $res = curl_exec($curl);
    
    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    curl_close($curl);
    
    $header = substr($res, 0, $headerSize);
    
    //echo "HEADER=".$header.END;
    //echo "URL=".$url.END;
    
    $outobj = new stdClass();
    
    $lines = explode("\r\n",$header);
    foreach($lines as $line)
    {
        $items = explode(": ",$line);
        if(isset($items[0]) and !empty($items[0]) and 
           isset($items[1]) and !empty($items[1]))
            $outobj->$items[0] = $items[1];
    }
    
    $outobj->ciphertext = substr($res, $headerSize);

    return $outobj;
}


function getMillisecond() {
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

function SendSMS($mobile, $content, 
            $timerTime = "", $customSmsId = "",
            $extendedCode = "", 
            $validPeriodtime= 120)
{   

    // 如果您的系统环境不是UTF-8，内容需要转码到UTF-8。如下：从gb2312转到了UTF-8
    // $content = mb_convert_encoding( $content,"UTF-8","gb2312");

    $item = new stdClass();
    $item->mobile   = $mobile;
    $item->content  = $content;
    
    /* 选填内容 */ 
    if("" != $timerTime)    $item->timerTime    = $timerTime;
    if("" != $customSmsId)  $item->customSmsId  = $customSmsId;
    if("" != $extendedCode) $item->extendedCode = $extendedCode;    
    
    $item->requestTime = getMillisecond();
    $item->requestValidPeriod = $validPeriodtime;
    
    $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

    echo "SendJson=".$json_data.END;

    $encryptObj = new MagicCrypt();
    $senddata = $encryptObj->encrypt($json_data);//加密结果
    
    $url = YM_SMS_ADDR.YM_SMS_SEND_URI;
    $resobj = http_request($url, $senddata);
    $resobj->plaintext = $encryptObj->decrypt($resobj->ciphertext);

    return $resobj;
}   
function SendBatchSMS($mobiles, $content, 
            $timerTime = "", $customSmsId = "",
            $extendedCode = "", 
            $validPeriodtime= 120)
{   
    $item = new stdClass();

    $smses = array();
    foreach($mobiles as $mobile)    $smses[] = $mobile;

    $item->smses   = $smses;

    // 如果您的系统环境不是UTF-8，内容需要转码到UTF-8。如下：从gb2312转到了UTF-8
    // $content = mb_convert_encoding( $content,"UTF-8","gb2312");

    $item->content  = $content;
    /* 选填内容 */ 
    if("" != $timerTime)    $item->timerTime    = $timerTime;
    if("" != $customSmsId)  $item->customSmsId  = $customSmsId;
    if("" != $extendedCode) $item->extendedCode = $extendedCode;    
    
    $item->requestTime = getMillisecond();
    $item->requestValidPeriod = $validPeriodtime;
    
    $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

    echo "SendJson=".$json_data.END;

    $encryptObj = new MagicCrypt();
    $senddata = $encryptObj->encrypt($json_data);//加密结果
    
    $url = YM_SMS_ADDR.YM_SMS_SEND_BATCH_URI;
    $resobj = http_request($url, $senddata);
    $resobj->plaintext = $encryptObj->decrypt($resobj->ciphertext);

    return $resobj;
}   

function sendBatchOnlySMS($mobiles, $content, 
            $timerTime = "", $customSmsId = "",
            $extendedCode = "", 
            $validPeriodtime= 120)
{   

    // 如果您的系统环境不是UTF-8，内容需要转码到UTF-8。如下：从gb2312转到了UTF-8
    // $content = mb_convert_encoding( $content,"UTF-8","gb2312");

    $item = new stdClass();

    $item->mobiles  = $mobiles;
    $item->content  = $content;
    /* 选填内容 */ 
    if("" != $timerTime)    $item->timerTime    = $timerTime;
    if("" != $extendedCode) $item->extendedCode = $extendedCode;    
    
    $item->requestTime = getMillisecond();
    $item->requestValidPeriod = $validPeriodtime;
    
    $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

    echo "SendJson=".$json_data.END;

    $encryptObj = new MagicCrypt();
    $senddata = $encryptObj->encrypt($json_data);//加密结果
    
    $url = YM_SMS_ADDR.YM_SMS_SEND_BATCHONLY_SMS_URI;
    $resobj = http_request($url, $senddata);
    $resobj->plaintext = $encryptObj->decrypt($resobj->ciphertext);

    return $resobj;
}   

function sendPersonalitySMS($mobiles, 
            $timerTime = "", $customSmsId = "",
            $extendedCode = "", 
            $validPeriodtime= 120)
{   

    // 如果您的系统环境不是UTF-8，内容需要转码到UTF-8。如下：从gb2312转到了UTF-8
    // $content = mb_convert_encoding( $content,"UTF-8","gb2312");

    $item = new stdClass();

    $smses = array();
    foreach($mobiles as $mobile)    $smses[] = $mobile;

    $item->smses   = $smses;

    /* 选填内容 */ 
    if("" != $timerTime)    $item->timerTime    = $timerTime;
    if("" != $customSmsId)  $item->customSmsId  = $customSmsId;
    if("" != $extendedCode) $item->extendedCode = $extendedCode;    
    
    $item->requestTime = getMillisecond();
    $item->requestValidPeriod = $validPeriodtime;
    
    $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

    echo "SendJson=".$json_data.END;

    $encryptObj = new MagicCrypt();
    $senddata = $encryptObj->encrypt($json_data);//加密结果
    
    $url = YM_SMS_ADDR.YM_SMS_SEND_PERSONALITY_SMS_URI;
    $resobj = http_request($url, $senddata);
    $resobj->plaintext = $encryptObj->decrypt($resobj->ciphertext);

    return $resobj;
}   

function getReport($number = 0, $validPeriodtime= 120)
{   


    $item = new stdClass();
    /* 选填内容 */ 
    if(0 != $number)    $item->number    = $number;

    $item->requestTime = getMillisecond();
    $item->requestValidPeriod = $validPeriodtime;
    
    $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

    echo "SendJson=".$json_data.END;

    $encryptObj = new MagicCrypt();
    $senddata = $encryptObj->encrypt($json_data);//加密结果
    
    $url = YM_SMS_ADDR.YM_SMS_GETREPORT_URI;
    $resobj = http_request($url, $senddata);
    $resobj->plaintext = $encryptObj->decrypt($resobj->ciphertext);

    return $resobj;
}  

function getMo($number = 0, $validPeriodtime= 120)
{   
    $item = new stdClass();
    /* 选填内容 */ 
    if(0 != $number)    $item->number    = $number;

    $item->requestTime = getMillisecond();
    $item->requestValidPeriod = $validPeriodtime;
    
    $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

    echo "SendJson=".$json_data.END;

    $encryptObj = new MagicCrypt();
    $senddata = $encryptObj->encrypt($json_data);//加密结果
    
    $url = YM_SMS_ADDR.YM_SMS_GETMO_URI;
    $resobj = http_request($url, $senddata);
    $resobj->plaintext = $encryptObj->decrypt($resobj->ciphertext);

    return $resobj;
}  

function getBalance($validPeriodtime= 120)
{   
    $item = new stdClass();

    $item->requestTime = getMillisecond();
    $item->requestValidPeriod = $validPeriodtime;
    
    $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

    echo "SendJson=".$json_data.END;

    $encryptObj = new MagicCrypt();
    $senddata = $encryptObj->encrypt($json_data);//加密结果
    
    $url = YM_SMS_ADDR.YM_SMS_GETBALANCE_URI;
    $resobj = http_request($url, $senddata);
    $resobj->plaintext = $encryptObj->decrypt($resobj->ciphertext);

    return $resobj;
}  
    
function run(){
    echo "***************测试单条短信发送***************".END;
    $resobj = SendSMS("18001000000", "【某某公司】您的验证码是123");/* 短信内容请以商务约定的为准，如果已经在通道端绑定了签名，则无需在这里添加签名 */
    $resobj->ciphertext = "";
    print_r($resobj);
    echo END;
    echo "***************返回值:".$resobj->result."***************".END;

    echo "***************测试多条短信发送(支持SMSID)***************".END;
    $mobiles = array();
    $mobiles[] = new stdClass();
    $mobiles[0]->mobile         = "18001000000";
    $mobiles[0]->customSmsId    = "";
    $mobiles[] = new stdClass();
    $mobiles[1]->mobile         = "18001000001";
    $mobiles[1]->customSmsId    = "";

    $resobj = SendBatchSMS($mobiles, "【某某公司】您的验证码是123");/* 短信内容请以商务约定的为准，如果已经在通道端绑定了签名，则无需在这里添加签名 */
    $resobj->ciphertext = "";
    print_r($resobj);
    echo END;
    echo "***************返回值:".$resobj->result."***************".END;

    echo "***************测试多条短信发送(不支持SMSID)***************".END;
    $mobiles = array();
    $mobiles[] = "18001000000";
    $mobiles[] = "18001000001";

    $resobj = sendBatchOnlySMS($mobiles, "【某某公司】您的验证码是123");
    $resobj->ciphertext = "";
    print_r($resobj);
    echo END;
    echo "***************返回值:".$resobj->result."***************".END;

    echo "***************测试个性短信接口***************".END;
    $mobiles = array();
    $mobiles[] = new stdClass();
    $mobiles[0]->mobile         = "18001000000";
    $mobiles[0]->customSmsId    = "";
    $mobiles[0]->content        = "我是个性1号";
    $mobiles[] = new stdClass();
    $mobiles[1]->mobile         = "18001000001";
    $mobiles[1]->customSmsId    = "";
    $mobiles[1]->content        = "我是个性2号";
    $mobiles[] = new stdClass();
    $mobiles[2]->mobile         = "18001000002";
    $mobiles[2]->customSmsId    = "";
    $mobiles[2]->content        = "我是个性3号";

    $resobj = sendPersonalitySMS($mobiles);
    $resobj->ciphertext = "";
    print_r($resobj);
    echo END;
    echo "***************返回值:".$resobj->result."***************".END;

    echo "***************测试状态报告接口***************".END;
    $resobj = getReport();
    $resobj->ciphertext = "";
    print_r($resobj);
    echo END;
    echo "***************返回值:".$resobj->result."***************".END;

    echo "***************测试上行接口***************".END;
    $resobj = getMo();
    $resobj->ciphertext = "";
    print_r($resobj);
    echo END;
    echo "***************返回值:".$resobj->result."***************".END;

    echo "***************测试余额接口***************".END;
    $resobj = getBalance();
    $resobj->ciphertext = "";
    print_r($resobj);
    echo END;
    echo "***************返回值:".$resobj->result."***************".END;
}
    
run();

?>
