<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Validator;
use App\YimeiSMS\MobileCode;

class AuthController extends ApiController
{
    private $tokenKey = 'auth_user';

    public function __construct()
    {
    }

    public function postLogin(MobileCode $mobileCode, Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/^1\d{10}$/',
            'code' => 'regex:/^\d{6}$/',
        ], [
            'mobile.required' => '手机号必须！',
            'mobile.regex' => '手机号格式不正确！',
            'code.regex' => '验证码格式不正确！',
        ]);
        
        if ($validator->fails()) {
            return $this->responseError($validator->errors()->first(), 400);
        }

        $mobile = $request->input('mobile');
        $code = $request->input('code');
        $password = $request->input('password');

        if ($code) {
            if ( ! $mobileCode->check($mobile, $code)) {
                return $this->responseError("验证码输入不正确，请重新输入！", 400);  
            }
            $user = User::findUserByMobile($mobile);
        } else {
            if (Auth::attempt(['mobile' => $mobile, 'password' => $password])) {
                $user = Auth::user();
            }
        }

        if ($user) {
            // 记录登录时间
            $user->login_at = Carbon::now();
            $user->saveOrFail();

            return $this->responseJson([
                'token' => $user->createToken($this->tokenKey)->accessToken,
            ]);
        }

        return $this->responseError('用户名或者密码错误！', 400);
    }

    public function postRegister(MobileCode $mobileCode, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/^1\d{10}$/|unique:users',
            'code' => 'required|regex:/^\d{6}$/',
            // 'password' => 'required',
//             'channel' => 'required',
        ], [
            'mobile.required' => '手机号必须！',
            'mobile.regex' => '手机号格式不正确！',
            'mobile.unique' => '手机号已被注册！',
            'code.required' => '验证码必须！',
            'code.regex' => '验证码号格式不正确！',
            // 'password.required' => '登录密码必须！',
        ]);
        
        if ($validator->fails()) {
            return $this->responseError($validator->errors()->first(), 400);
        }

        $mobile = $request->input('mobile');
        $code = $request->input('code');
        if ( ! $mobileCode->check($mobile, $code)) {
            return $this->responseError("验证码输入不正确，请重新输入！", 400);  
        }

        $channel = $request->input('channel');
        $password = $request->input('password', '12345678');
        $password = bcrypt($password);

        try {
            $user = new User([
                'name' => $mobile,
                'email' => $mobile . '@null.null',
                'mobile' => $mobile,
                'password' => $password,
                'login_at' => Carbon::now(),
                'channel' => $channel,
            ]);
            $user->saveOrFail();
        } catch(\Exception $e) {
            return $this->responseError($e->getMessage(), 400);  
        }

        return $this->responseJson([
            'token' => $user->createToken($this->tokenKey)->accessToken,
        ]);
    }

    public function postSendCode(MobileCode $mobileCode, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/^1\d{10}$/',
            'captcha' => 'required|captcha'
        ], [
            'captcha.required' => '验证码必须！',
            'captcha.captcha' => '验证答案错误！',
            'mobile.required' => '手机号必须！',
            'mobile.regex' => '手机号格式不正确！',
        ]);

        if ($validator->fails()) {
            return $this->responseError($validator->errors()->first(), 400);
        }

        $mobile = $request->input('mobile');

        try {
            $data = $mobileCode->send($mobile);
        } catch(\Exception $e) {
            return $this->responseError($e->getMessage(), 400);  
        }

        return $this->responseJson([
            'message' => "短信成功发送至 {$mobile} ，请注意查收！",
            'retry_second' => $data['retry_second'],
            'num' => $data['num'],
        ]);
    }

    public function getUser(Request $request)
    {
        return $this->responseJson($request->user());
    }

    public function getCaptchaSrc()
    {
        return captcha_src();
    }

    public function getCaptchaImg()
    {
        return captcha_img();
    }
}
