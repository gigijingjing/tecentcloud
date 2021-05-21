<?php

namespace App\AliyunSMS;

use Curder\LaravelAliyunSms\AliyunSms;
use Illuminate\Support\Facades\Cache;

class MobileCode
{
    private $sms;
    private $minutes = 30;
    private $tplCode = '';

    public function __construct(AliyunSms $sms) 
    {
        $this->sms = $sms;
    }

    public function send($mobile)
    {
        $code = rand(100000, 999999);

        $this->sms->send($mobile, $this->tplCode, [
            'code' => $code
        ]);

        $this->setCache($mobile, $code);

    }

    public function check($mobile, $code)
    {
        $this->removeCache($mobile);
    }

    private function setCache($mobile, $code)
    {
        Cache::put($this->getKey(), $code, $this->minutes);
    }

    private function removeCache($mobile)
    {
        Cache::forget($this->getKey());
    }

    private function getKey($mobile) 
    {
        return 'send_code_by_' + $mobile;
    }
}
