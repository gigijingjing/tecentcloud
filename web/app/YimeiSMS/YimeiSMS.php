<?php

namespace App\YimeiSMS;

class YimeiSMS
{
    private $signature = '多易贷';
    private $config;
    private $gzip = true;

    public function __construct() 
    {
        $this->config = config('yimeisms');
    }

    public function send($mobile, $message)
    {
        $content = $this->createMessage($message);
        $timestamp = date("YmdHis");
        $sign = md5($this->config['appid'] . $this->config['secret'] . $timestamp);
        $data = array(
            "appId" => $this->config['appid'],
            "timestamp" => $timestamp,
            "sign" => $sign,
            "mobiles" => $mobile,
            "content" =>  $content,
            // "customSmsId" => "10001",
            // "timerTime" => date("yyyyMMddHHmmss"),
            // "extendedCode" => "456789"
        );
        $url = $this->config['server'] . '/simpleinter/sendSMS';
        return $this->httpRequest($url, $data);
    }

    private function createMessage($message)
    {
        return "【{$this->signature}】" . $message;
    }

    private function httpRequest($url, $data)
    {
        $data = http_build_query($data);	
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return json_decode($output);
    }
}
