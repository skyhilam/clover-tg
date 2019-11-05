<?php

namespace Clover\CloverTg;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class CloverTg {
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('clover-tg.url'),
            'timeout' => 30.0,
        ]);
    }

    public function send($message, $token = null)
    {
        $message = is_array($message)? $this->arrayToString($message): $message;
        $this->sendMessage([
            'token' => $token ?? config('clover-tg.token'),
            'message' => $message
        ]);
    }

    public function sendWithCallback($message, $callback, $ex_time = 60, $token = null, $options = [])
    {
        $message = is_array($message)? $this->arrayToString($message): $message;
        $this->sendMessage([
            'token' => $token ?? config('clover-tg.token'),
            'message' => $message,
            'ex_time' => $ex_time,
            'callback' => $callback,
            'options' => $options,
        ]);
    }

    protected function sendMessage($data)
    {
        try {
            $this->client->post('/send', [
                'form_params' => $data
            ]);
        } catch (ClientException $e) {
            \Log::error($e->getMessage());
        }
    }

    protected function arrayToString($data, $glue = PHP_EOL)
    {
        return implode($glue, array_map(
            function ($v, $k) {
                if (is_array($v)) {
                    return sprintf("%s: %s", $k, $this->arrayToString($v));
                }
                return sprintf("%s: %s", $k, $v);
            },
            $data,
            array_keys($data)
        ));
    }
}
