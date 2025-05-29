<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CaptchaService
{
    protected $service;

    public function __construct()
    {
        $this->service = config('captcha.default'); // 根據配置選擇服務
    }

    public function verify($token)
    {
        if ($this->service === 'recaptcha') {
            return $this->verifyRecaptcha($token);
        } elseif ($this->service === 'hcaptcha') {
            return $this->verifyHcaptcha($token);
        }
        return false;
    }

    protected function verifyRecaptcha($token)
    {

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('captcha.recaptcha.secret_key'),
            'response' => $token,
        ]);
        $result = json_decode($response->getBody(), true);
        return $result['success'];
    }

    protected function verifyHcaptcha($token)
    {
        $response =  Http::asForm()->post('https://hcaptcha.com/siteverify', [
            'form_params' => [
                'secret' => config('captcha.hcaptcha.secret_key'),
                'response' => $token,
            ],
        ]);

        $result = json_decode($response->getBody(), true);
        return $result['success'];
    }
}
