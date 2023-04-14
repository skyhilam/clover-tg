<?php

namespace Clover\CloverTg\Traits;

trait AttributesTrait
{
  protected $token;

  protected $message;

  protected $message_id;

  protected $callback;

  protected $ex_time = 60; //second

  protected $options = [];


  /**
   * 設置通知token
   * 
   * @param string
   * @return CloverTg $this
   * */
  public function token($token)
  {
    $this->token = $token;

    return $this;
  }

  public function messageId($message_id)
  {
    $this->message_id = $message_id;

    return $this;
  }

  /** 
   * 設置通知資料
   * 
   * @param array|string $data
   * @return CloverTg $this
   * */
  public function message($data)
  {
    $this->message = is_array($data) ? $this->arrayToString($data) : $data;

    return $this;
  }

  public function callback($callback)
  {
    $this->callback = $callback;

    return $this;
  }

  public function exTime($ex_time)
  {
    $this->ex_time = $ex_time;

    return $this;
  }

  public function options($options)
  {
    $this->options = $options;

    return $this;
  }

  protected function arrayToString(array $data, $glue = PHP_EOL)
  {
    // laravel array to string
    return collect($data)->map(function ($item, $key) {
      if (is_array($item)) {
        return $this->arrayToString($item);
      }

      if (is_numeric($key)) {
        return $item;
      }

      return $key . ' : ' . $item;
    })->implode($glue);
  }

  protected function formdata()
  {
    return [
      'token' => $this->getToken(),
      'message' => $this->message,
      'message_id' => $this->message_id,
      'callback' => $this->callback,
      'ex_time' => $this->ex_time,
      'options' => $this->options
    ];
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
}
