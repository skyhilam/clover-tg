# Clover Telegram SDK

Laravel 套件，用於發送 Telegram 消息、圖片和互動式會話。

## 功能特色

- 📨 發送文字訊息
- 🖼️ 發送單張/多張圖片
- 🔘 發送帶按鈕的互動式會話
- ✏️ 編輯已發送的訊息
- ⚙️ 自定義按鈕配置
- 🔄 鏈式 API 調用
- 🛡️ 完善的錯誤處理

## 系統需求

- PHP >= 7.2
- Laravel >= 6.0
- Guzzle HTTP >= 7.0

## 安裝

### 1. 配置 Composer

在 `composer.json` 中添加私有倉庫：

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:skyhilam/clover-tg.git"
    }
  ]
}
```

### 2. 安裝套件

```bash
composer require clover/clover-tg
```

### 3. 發布配置文件

```bash
php artisan vendor:publish --provider="Clover\CloverTg\ServiceProvider" --tag="config"
```

### 4. 配置環境變數

在 `.env` 文件中添加：

```env
# Telegram Token（從 Bot 的 /token 指令獲取）
TELEGRAM_TOKEN=your_token_here

# API 服務地址（可選，預設為 https://tg.iclover.net）
TELEGRAM_URL=https://tg.iclover.net
```

## 使用方式

### 導入 Facade

```php
use Clover\CloverTg\Facades\CloverTg;
```

### 發送文字訊息

```php
// 簡單發送
CloverTg::send('Hello World!');

// 指定 Token 發送
CloverTg::send('Hello World!', $customToken);

// 鏈式調用
CloverTg::message('Hello World!')
    ->token($customToken)
    ->notify();
```

### 發送帶回調按鈕的會話

用戶點擊按鈕後，系統會 POST 到您指定的 callback URL。

```php
// 發送默認按鈕（確認/取消）
CloverTg::message('請確認此操作')
    ->callback('https://your-server.com/webhook')
    ->dispatch();

// 使用便捷方法
CloverTg::sendWithCallback(
    '請確認此操作',                      // 訊息內容
    'https://your-server.com/webhook',   // 回調 URL
    ['ex_time' => 120]                   // 選項：過期時間 120 秒
);
```

### 發送自定義按鈕會話

```php
// 鏈式調用
CloverTg::message('請選擇操作')
    ->callback('https://your-server.com/webhook')
    ->buttons([
        ['id' => 'approve', 'text' => '✅ 批准'],
        ['id' => 'reject', 'text' => '❌ 拒絕'],
        ['id' => 'pending', 'text' => '⏳ 稍後處理'],
    ])
    ->exTime(120)  // 過期時間（秒）
    ->notify();

// 使用便捷方法
CloverTg::sendWithCallback('請選擇操作', 'https://your-server.com/webhook', [
    'ex_time' => 120,
    'buttons' => [
        ['id' => 'approve', 'text' => '✅ 批准'],
        ['id' => 'reject', 'text' => '❌ 拒絕'],
    ],
    'options' => [
        'headers' => ['Authorization' => 'Bearer xxx']
    ]
]);
```

### 回調數據格式

當用戶點擊按鈕時，您的 webhook 會收到：

```json
{
  "message": "approve",
  "chat_id": 123456789,
  "message_id": 100
}
```

### 編輯訊息

```php
// 編輯文字訊息
CloverTg::edit($messageId, '更新後的內容');

// 編輯圖片標題
CloverTg::editCaption($messageId, '更新後的圖片說明');

// 指定 Token 編輯
CloverTg::edit($messageId, '更新後的內容', $customToken);
```

### TOTP 驗證

用於驗證用戶輸入的動態驗證碼（從 Telegram Bot 的 `/totp` 指令獲取）。

```php
// 簡單驗證（返回 bool）
if (CloverTg::verifyTotp($code)) {
    // 驗證成功
    return redirect()->intended('/dashboard');
} else {
    // 驗證失敗
    return back()->withErrors(['code' => '驗證碼無效']);
}

// 指定 Token 驗證
if (CloverTg::verifyTotp($code, $userToken)) {
    // 驗證成功
}

// 獲取詳細結果
$result = CloverTg::verifyTotpWithResult($code);
if ($result) {
    // $result = ['token' => 'xxx']
}
```

### 發送圖片

```php
// 發送單張圖片
CloverTg::sendPhoto($chatId, 'https://example.com/image.jpg', '圖片說明');

// 發送多張圖片
CloverTg::sendPhotos($chatId, [
    'https://example.com/image1.jpg',
    'https://example.com/image2.jpg',
], '相簿說明');
```

### 錯誤處理

```php
// 自定義錯誤處理器
CloverTg::onError(function ($exception, $errorInfo) {
    Log::channel('telegram')->error($exception->getMessage(), $errorInfo);
})->send('測試訊息');

// 檢查發送結果
CloverTg::send('測試訊息');

if (CloverTg::isSuccess()) {
    $response = CloverTg::getLastResponse();
    // 處理成功響應
} else {
    $error = CloverTg::getLastError();
    // 處理錯誤
}
```

## API 參考

### 發送方法

| 方法 | 說明 |
|------|------|
| `send($message, $token = null)` | 發送文字訊息 |
| `sendWithCallback($message, $callback, $options = [], $token = null)` | 發送帶回調的訊息 |
| `notify()` | 發送當前配置的訊息到 `/send` |
| `dispatch()` | 發送當前配置的訊息到 `/dispatch` |

### 編輯方法

| 方法 | 說明 |
|------|------|
| `edit($message_id, $message, $token = null)` | 編輯文字訊息 |
| `editCaption($message_id, $caption, $token = null)` | 編輯圖片標題 |

### TOTP 方法

| 方法 | 說明 |
|------|------|
| `verifyTotp($code, $token = null)` | 驗證 TOTP 驗證碼，返回 bool |
| `verifyTotpWithResult($code, $token = null)` | 驗證並返回詳細結果 |

### 圖片方法

| 方法 | 說明 |
|------|------|
| `sendPhoto($chatId, $url, $caption)` | 發送單張圖片 |
| `sendPhotos($chatId, $urls, $caption)` | 發送多張圖片 |

### 鏈式設置方法

| 方法 | 說明 |
|------|------|
| `token($token)` | 設置 Token |
| `message($message)` | 設置訊息內容 |
| `messageId($id)` | 設置訊息 ID（編輯時使用） |
| `callback($url)` | 設置回調 URL |
| `buttons($buttons)` | 設置自定義按鈕 |
| `exTime($seconds)` | 設置過期時間 |
| `options($options)` | 設置回調請求選項 |

### 狀態方法

| 方法 | 說明 |
|------|------|
| `onError($handler)` | 設置錯誤處理器 |
| `getLastError()` | 獲取最後一次錯誤 |
| `getLastResponse()` | 獲取最後一次響應 |
| `isSuccess()` | 檢查最後一次請求是否成功 |
| `clearState()` | 清除狀態 |

## 配置文件

`config/clover-tg.php`:

```php
return [
    // Telegram Token
    'token' => env('TELEGRAM_TOKEN', ''),
    
    // API 服務 URL
    'url' => env('TELEGRAM_URL', 'https://tg.iclover.net')
];
```

## 項目結構

```
php/
├── config/
│   └── clover-tg.php        # 配置文件
├── src/
│   ├── CloverTg.php         # 主類
│   ├── ServiceProvider.php  # Laravel 服務提供者
│   ├── Facades/
│   │   └── CloverTg.php     # Facade
│   └── Traits/
│       ├── AttributesTrait.php              # 屬性設置
│       └── CloverTelegramNotification.php   # 通知 Trait
├── tests/
│   └── CloverTgTest.php     # 單元測試
├── composer.json
├── phpunit.xml
└── README.md
```

## 測試

```bash
# 運行測試
composer phpunit

# 或直接使用 PHPUnit
./vendor/bin/phpunit
```

## 使用場景示例

### 訂單確認

```php
CloverTg::message("訂單 #{$order->id}\n金額：{$order->amount}")
    ->callback(route('api.order.confirm', $order))
    ->buttons([
        ['id' => 'confirm', 'text' => '✅ 確認出貨'],
        ['id' => 'cancel', 'text' => '❌ 取消訂單'],
    ])
    ->exTime(300)  // 5 分鐘內有效
    ->dispatch();
```

### 系統警報

```php
CloverTg::send("⚠️ 系統警報\n\n" . $alertMessage);
```

### 圖片審核

```php
CloverTg::sendPhoto($reviewerChatId, $imageUrl, "請審核此圖片")
    // 需要結合 TypeScript API 的 callback 功能
```

### 驗證碼通知

```php
CloverTg::send("您的驗證碼是：{$code}，5分鐘內有效。");
```

## 錯誤碼說明

| HTTP 狀態碼 | 說明 |
|------------|------|
| 200 | 成功 |
| 400 | 請求錯誤 |
| 422 | 驗證失敗 |
| 429 | 請求過於頻繁 |
| 500 | 伺服器錯誤 |

## 注意事項

1. **Token 保密**：請勿將 Token 提交到版本控制系統
2. **回調 URL**：必須是可公開訪問的 HTTPS URL
3. **按鈕 ID**：自定義按鈕的 ID 應該是唯一的英文字符串
4. **過期時間**：會話按鈕會在過期後失效，建議設置合理的過期時間
5. **PHP 版本**：本套件支援 PHP 7.2+，不使用 PHP 8.0+ 的新語法特性

## License

MIT
