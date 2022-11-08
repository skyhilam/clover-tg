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
  /**
   * 發送通知
   * 
   * 
   * */
  public function notify()
  {
    $this->sendMessage($this->formdata());
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
      ->exTime($ex_time)
      ->callback($callback)
      ->options($options)
      ->notify();

    // $message = $this->dataformated($message);
    // $this->sendMessage([
    //     'token' => $this->getToken($token),
    //     'message' => $message,
    //     'ex_time' => $ex_time,
    //     'callback' => $callback,
    //     'options' => $options,
    // ]);
  }


  /**
   * send photo
   * 
   * @param string chatid
   * @param string url
   * @param string caption
   * 
   * **/
  public function sendPhoto($chatid, string $url, string $caption)
  {
    try {
      $this->client->post('/send/photo', [
        'form_params' => [
          'chat_id' => $chatid,
          'url' => $url,
          'caption' => $caption
        ]
      ]);
    } catch (ClientException $e) {
      \Log::error($e->getMessage());
    }
  }

  /**
   * send photos
   * 
   * @param string chatid
   * @param array|string urls
   * @param string caption
   * 
   * **/
  public function sendPhotos($chatid, $urls, string $caption)
  {
    
    if (is_string($urls))
      return $this->sendPhoto($chatid, $urls, $caption);

    try {
      $this->client->post('/send/photos', [
        'form_params' => [
          'chat_id' => $chatid,
          'urls' => $urls,
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
