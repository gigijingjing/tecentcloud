<?php
namespace App\YimeiSMS;

use Illuminate\Support\Facades\Cache;

class MobileCode
{
    private $sms;
    // 过期时间（分钟）
    private $expiredMinutes = 30;
    // 重试时间（秒）
    private $retrySecond = 60;
    // 每天最多发送条数
    private $num = 10;

    public function __construct(YimeiSMS $sms) 
    {
        $this->sms = $sms;
    }

    public function send($mobile)
    {
        $num = 0;
        $data = $this->getCache($mobile);
        if ($data = $this->getCache($mobile)) {
            $num = $data['num'];
            if ($data['code'] && $data['code']['retry_time'] >= time()) {
                $second = $data['code']['retry_time'] - time();
                throw new \Exception("发送过于频繁，请 {$second}s 后再试！");
            }
            if ($num == $this->num) {
                throw new \Exception("抱歉已达到24小时内的最大发送次数！");
            }
            $num ++;
        }
        
        $code = rand(100000, 999999);

        $message = "你好，你的验证码为 {$code} ，有效时间为 {$this->expiredMinutes}分钟，打死也不能告诉别人。";

        $data = $this->sms->send($mobile, $message);

        $this->setCache($mobile, $code, $num);

        return [
            'retry_second' => $this->retrySecond,
            'num' => ($this->num - $num),
        ];
    }

    public function check($mobile, $code)
    {
        $data = $this->getCache($mobile);
        if ($data['code'] && $code == $data['code']['code']) {
            $this->removeCache($mobile);
            return true;
        }
        return false;
    }

    private function getCache($mobile)
    {
        $code = Cache::get($this->getKey($mobile));
        $num = Cache::get($this->getKey($mobile) . '_num');
        return [
            'code' => $code,
            'num' => $num['num'],
        ];
    }

    private function setCache($mobile, $code, $num)
    {
        $retryTime = (time() + $this->retrySecond);
        Cache::put($this->getKey($mobile), [
            'code' => $code,
            'retry_time' => $retryTime,
        ], $this->expiredMinutes);

        Cache::put($this->getKey($mobile) . '_num', [
            'num' => $num,
        ], 1440);
    }

    private function removeCache($mobile)
    {
        Cache::forget($this->getKey($mobile));
    }

    private function getKey($mobile) 
    {
        return 'send_code_by_' . $mobile;
    }
}
