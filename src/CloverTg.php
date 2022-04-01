<?php

namespace Clover\CloverTg;

use Clover\CloverTg\Traits\AttributesTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class CloverTg
{
    use AttributesTrait;

    protected $client;

    protected string $message;
    protected string $token;

    public function __construct()
    {
        $this->token(config('clover-tg.token'));

        $this->client = new Client([
            'base_uri' => config('clover-tg.url'),
            'timeout' => 30.0,
        ]);
    }

    //  news v1.1
    /** 
     * 設置通知資料
     * 
     * @param array|string $data
     * @return CloverTg $this
     * */
    public function message($data)
    {
        $this->message = $this->dataformated($data);

        return $this;
    }

    /**
     * 設置通知token
     * 
     * @param string
     * @return CloverTg $this
     * */
    public function token(string $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * 發送通知
     * 
     * 
     * */
    public function notify(array $options = null)
    {
        $this->sendMessage(
            array_merge(
                [
                    'token' => $this->getToken(),
                    'message' => $this->message,
                ],
                $options
            )
        );
    }


    public function dispatch()
    {
        $this->sendMessage($this->formdata(), '/dispatch');
    }

    public function send($message, $token = null)
    {
        $this->token($token)
            ->message($message)
            ->notify();
    }

    public function sendWithCallback($message, $callback, $ex_time = 60, $token = null, $options = [])
    {
        $this->token($token)
            ->message($message)
            ->notify([
                'ex_time' => $ex_time,
                'callback' => $callback,
                'options' => $options,
            ]);

        // $message = $this->dataformated($message);
        // $this->sendMessage([
        //     'token' => $this->getToken($token),
        //     'message' => $message,
        //     'ex_time' => $ex_time,
        //     'callback' => $callback,
        //     'options' => $options,
        // ]);
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

    //  news v1.1

    /**
     * 獲取Token
     * 
     * @return string
     * */
    protected function getToken(): string
    {
        return $this->token ?? config('clover-tg.token');
    }

    /** 
     * 格式化資料
     * 
     * @param string|array $data
     * @return string
     * */
    protected function dataformated($data): string
    {
        return is_array($data) ? $this->arrayToString($data) : $data;
    }
}
