<?php

namespace Clover\CloverTg;

use Clover\CloverTg\Traits\AttributesTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class CloverTg
{
  use AttributesTrait;

  protected $client;
  protected $errorHandler = null;
  protected $lastError = null;
  protected $lastResponse = null;

  public function __construct()
  {
    $this->token(config('clover-tg.token'));
    $this->client = new Client([
      'base_uri' => config('clover-tg.url'),
      'timeout' => 30.0,
    ]);
  }

  // ==================== 狀態管理 ====================

  public function onError($handler) { $this->errorHandler = $handler; return $this; }
  public function getLastError() { return $this->lastError; }
  public function getLastResponse() { return $this->lastResponse; }
  public function isSuccess() { return $this->lastError === null && $this->lastResponse !== null; }
  public function clearState() { $this->lastError = $this->lastResponse = null; return $this; }

  // ==================== 發送方法 ====================

  public function notify() { return $this->sendMessage($this->formdata()); }
  public function dispatch() { return $this->sendMessage($this->formdata(), '/dispatch'); }

  public function send($message, $token = null)
  {
    return $this->token($token)->message($message)->notify();
  }

  /**
   * 發送帶回調/按鈕的訊息
   * @param string $message 訊息
   * @param string $callback 回調 URL
   * @param array $options 選項 ['ex_time' => 60, 'buttons' => [], 'options' => []]
   * @param string|null $token Token
   */
  public function sendWithCallback($message, $callback, $options = [], $token = null)
  {
    $this->token($token)
      ->message($message)
      ->callback($callback)
      ->exTime($options['ex_time'] ?? 60)
      ->options($options['options'] ?? []);
    
    if (!empty($options['buttons'])) {
      $this->buttons($options['buttons']);
    }
    
    return $this->notify();
  }

  // ==================== 編輯方法 ====================

  public function edit($message_id, $message, $token = null)
  {
    return $this->token($token)->messageId($message_id)->message($message)->request('/edit', $this->formdata());
  }

  public function editCaption($message_id, $caption, $token = null)
  {
    return $this->request('/edit', [
      'token' => $token ?: $this->getToken(),
      'message_id' => $message_id,
      'caption' => $caption
    ]);
  }

  // ==================== 圖片方法 ====================

  public function sendPhoto($chatid, string $url, string $caption)
  {
    return $this->request('/send/photo', compact('chatid', 'url', 'caption') + ['chat_id' => $chatid]);
  }

  public function sendPhotos($chatid, $urls, string $caption)
  {
    return is_string($urls) 
      ? $this->sendPhoto($chatid, $urls, $caption)
      : $this->request('/send/photos', ['chat_id' => $chatid, 'urls' => $urls, 'caption' => $caption]);
  }

  // ==================== 內部方法 ====================

  protected function sendMessage($data, $path = '/send') { return $this->request($path, $data); }

  protected function request($path, $data)
  {
    $this->clearState();

    try {
      $response = $this->client->post($path, ['form_params' => $data]);
      $body = $response->getBody()->getContents();
      
      $this->lastResponse = [
        'status' => $response->getStatusCode(),
        'data' => json_decode($body, true),
        'path' => $path
      ];

      return $this->lastResponse;
    } catch (ClientException $e) {
      $this->handleError($e, ['path' => $path, 'type' => 'client_error']);
    } catch (GuzzleException $e) {
      $this->handleError($e, ['path' => $path, 'type' => 'network_error']);
    } catch (\Exception $e) {
      $this->handleError($e, ['path' => $path, 'type' => 'unknown_error']);
    }
    return null;
  }

  protected function handleError($e, $context = [])
  {
    $errorInfo = [
      'message' => $e->getMessage(),
      'code' => $e->getCode(),
      'context' => $context,
      'time' => date('Y-m-d H:i:s')
    ];

    if ($e instanceof ClientException && $e->hasResponse()) {
      $response = $e->getResponse();
      $body = $response->getBody()->getContents();
      $errorInfo['response'] = [
        'status' => $response->getStatusCode(),
        'body' => json_decode($body, true) ?: $body
      ];
    }

    $this->lastError = $errorInfo;

    is_callable($this->errorHandler)
      ? call_user_func($this->errorHandler, $e, $errorInfo)
      : \Log::error('[CloverTg] ' . $e->getMessage(), $errorInfo);
  }
}
