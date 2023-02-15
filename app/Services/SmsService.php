<?php

namespace App\Services;

use Kavenegar\KavenegarApi;

class SmsService
{
    private const SENDER = '100006935';

    private KavenegarApi $client;

    public function __construct()
    {
        $this->client = new KavenegarApi(env('KAVENEGAR_API_KEY'));
    }

    public function send(int $phone, string $message): void
    {
        $this->client->Send(self::SENDER, $phone, $message);
    }

    public function otp(int $phone, string $code): void
    {
        $this->send($phone, trans('messages.sms.otp', ['code' => $code]));
    }

//    public function reservation (int $phone): void
//    {
//        $this->send($phone, trans('messages.sms.reservation'));
//    }
}
