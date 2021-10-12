<?php

namespace Clover\CloverTg;

use Clover\CloverTg\Traits\AttributesTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class CloverTg
{
    use AttributesTrait;

    protected $client;

    public function __construct()
    {
        $this->token(config('clover-tg.token'));

        $this->client = new Client([
            'base_uri' => config('clover-tg.url'),
            'timeout' => 30.0,
        ]);
    }


    public function dispatch()
    {
        $this->sendMessage($this->formdata(), '/dispatch');
    }

    public function send($message, $token = null)
    {
        $message = is_array($message) ? $this->arrayToString($message) : $message;
        $this->sendMessage([
            'token' => $token ?? config('clover-tg.token'),
            'message' => $message
        ]);
    }

    public function sendWithCallback($message, $callback, $ex_time = 60, $token = null, $options = [])
    {
        $message = is_array($message) ? $this->arrayToString($message) : $message;
        $this->sendMessage([
            'token' => $token ?? config('clover-tg.token'),
            'message' => $message,
            'ex_time' => $ex_time,
            'callback' => $callback,
            'options' => $options,
        ]);
    }

    public function sendPhoto($chatid, $photo_url, $caption)
    {
        try {
            $this->client->post('/send/photo', [
                'form_params' => [
                    'chat_id' => $chatid,
                    'url' => $photo_url,
                    'caption' => $caption
                ]
            ]);
        } catch (ClientException $e) {
            \Log::error($e->getMessage());
        }
    }

    protected function sendMessage($data, $path = '/send')
    {
        try {
            $this->client->post($path, [
                'form_params' => $data
            ]);
        } catch (ClientException $e) {
            \Log::error($e->getMessage());
        }
    }

}
