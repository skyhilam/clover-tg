<?php

namespace Clover\CloverTg\Tests;

use Clover\CloverTg\CloverTg;
use Illuminate\Http\Client\Factory as HttpFactory;
use PHPUnit\Framework\TestCase;

class CloverTgTest extends TestCase
{
    protected $cloverTg;
    protected $httpFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpFactory = new HttpFactory();
        $this->cloverTg = $this->createCloverTg();
    }

    /**
     * 創建 CloverTg 實例
     */
    protected function createCloverTg()
    {
        // 創建 CloverTg 實例，覆蓋構造函數避免依賴 Laravel config
        $cloverTg = new class extends CloverTg {
            public function __construct()
            {
                // 不調用父構造函數，避免依賴 Laravel config
                $this->token = 'test-token';
                $this->baseUrl = 'https://api.example.com';
            }
            
            // 覆蓋 getToken 方法以避免調用 Laravel config()
            protected function getToken(): string
            {
                return $this->token ?? 'test-token';
            }
        };

        $cloverTg->setHttpClient($this->httpFactory);

        return $cloverTg;
    }

    /**
     * 設置 Http::fake 模擬成功回應
     */
    protected function fakeSuccessResponse($data = ['data' => 'ok'])
    {
        $this->httpFactory->fake([
            '*' => $this->httpFactory->response($data, 200)
        ]);
    }

    /**
     * 設置 Http::fake 模擬錯誤回應
     */
    protected function fakeErrorResponse($data = ['error' => 'Bad Request'], $status = 400)
    {
        $this->httpFactory->fake([
            '*' => $this->httpFactory->response($data, $status)
        ]);
    }

    // ==================== 基本功能測試 ====================

    /**
     * @test
     */
    public function it_can_set_token()
    {
        $this->fakeSuccessResponse();
        $result = $this->cloverTg->token('custom-token');
        
        $this->assertSame($this->cloverTg, $result);
    }

    /**
     * @test
     */
    public function it_can_set_message()
    {
        $this->fakeSuccessResponse();
        $result = $this->cloverTg->message('Test message');
        
        $this->assertSame($this->cloverTg, $result);
    }

    /**
     * @test
     */
    public function it_can_set_message_id()
    {
        $this->fakeSuccessResponse();
        $result = $this->cloverTg->messageId(12345);
        
        $this->assertSame($this->cloverTg, $result);
    }

    /**
     * @test
     */
    public function it_can_set_callback()
    {
        $this->fakeSuccessResponse();
        $result = $this->cloverTg->callback('https://example.com/callback');
        
        $this->assertSame($this->cloverTg, $result);
    }

    /**
     * @test
     */
    public function it_can_set_ex_time()
    {
        $this->fakeSuccessResponse();
        $result = $this->cloverTg->exTime(120);
        
        $this->assertSame($this->cloverTg, $result);
    }

    /**
     * @test
     */
    public function it_can_set_options()
    {
        $this->fakeSuccessResponse();
        $result = $this->cloverTg->options(['key' => 'value']);
        
        $this->assertSame($this->cloverTg, $result);
    }

    /**
     * @test
     */
    public function it_can_set_buttons()
    {
        $this->fakeSuccessResponse();
        $buttons = [
            ['id' => 'approve', 'text' => '批准'],
            ['id' => 'reject', 'text' => '拒絕'],
        ];
        
        $result = $this->cloverTg->buttons($buttons);
        
        $this->assertSame($this->cloverTg, $result);
    }

    // ==================== 鏈式調用測試 ====================

    /**
     * @test
     */
    public function it_supports_chained_calls()
    {
        $this->fakeSuccessResponse();
        $result = $this->cloverTg
            ->token('test-token')
            ->message('Test message')
            ->messageId(12345)
            ->callback('https://example.com/callback')
            ->exTime(120)
            ->options(['key' => 'value'])
            ->buttons([['id' => 'test', 'text' => 'Test']]);
        
        $this->assertSame($this->cloverTg, $result);
    }

    // ==================== 發送功能測試 ====================

    /**
     * @test
     */
    public function it_can_send_message()
    {
        $this->fakeSuccessResponse(['data' => ['message_id' => 123]]);

        $cloverTg = $this->createCloverTg();
        $result = $cloverTg->send('Hello World!');
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_can_notify()
    {
        $this->fakeSuccessResponse(['data' => 'ok']);

        $cloverTg = $this->createCloverTg();
        $result = $cloverTg->message('Test notification')->notify();
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_can_dispatch()
    {
        $this->fakeSuccessResponse(['data' => 'ok']);

        $cloverTg = $this->createCloverTg();
        $result = $cloverTg->message('Test dispatch')->dispatch();
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_can_send_with_callback()
    {
        $this->fakeSuccessResponse(['data' => ['message_id' => 123]]);

        $cloverTg = $this->createCloverTg();
        // 新的統一 API：第三個參數為選項陣列
        $result = $cloverTg->sendWithCallback(
            'Please confirm',
            'https://example.com/callback',
            ['ex_time' => 60]
        );
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_can_send_with_buttons()
    {
        $this->fakeSuccessResponse(['data' => ['message_id' => 123]]);

        $cloverTg = $this->createCloverTg();
        $buttons = [
            ['id' => 'approve', 'text' => '批准'],
            ['id' => 'reject', 'text' => '拒絕'],
        ];

        // 使用新的統一 API：sendWithCallback 的第三個參數為選項陣列
        $result = $cloverTg->sendWithCallback(
            'Please choose',
            'https://example.com/callback',
            ['buttons' => $buttons, 'ex_time' => 60]
        );
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    // ==================== 圖片發送測試 ====================

    /**
     * @test
     */
    public function it_can_send_photo()
    {
        $this->fakeSuccessResponse(['data' => ['message_id' => 123]]);

        $cloverTg = $this->createCloverTg();
        $result = $cloverTg->sendPhoto(
            '123456789',
            'https://example.com/image.jpg',
            'Image caption'
        );
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_can_send_photos()
    {
        $this->fakeSuccessResponse(['data' => ['message_id' => 123]]);

        $cloverTg = $this->createCloverTg();
        $result = $cloverTg->sendPhotos(
            '123456789',
            [
                'https://example.com/image1.jpg',
                'https://example.com/image2.jpg',
            ],
            'Multiple images'
        );
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_sends_single_photo_when_urls_is_string()
    {
        $this->fakeSuccessResponse(['data' => ['message_id' => 123]]);

        $cloverTg = $this->createCloverTg();
        $result = $cloverTg->sendPhotos(
            '123456789',
            'https://example.com/image.jpg',
            'Single image as string'
        );
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    // ==================== 編輯功能測試 ====================

    /**
     * @test
     */
    public function it_can_edit_message()
    {
        $this->fakeSuccessResponse(['data' => true]);

        $cloverTg = $this->createCloverTg();
        $result = $cloverTg->edit(12345, 'Updated message');
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_can_edit_caption()
    {
        $this->fakeSuccessResponse(['data' => true]);

        $cloverTg = $this->createCloverTg();
        $result = $cloverTg->editCaption(12345, 'Updated caption');
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    // ==================== 錯誤處理測試 ====================

    /**
     * @test
     */
    public function it_handles_client_error()
    {
        $this->fakeErrorResponse(['error' => 'Bad Request'], 400);

        $cloverTg = $this->createCloverTg();
        // 設置自定義錯誤處理器以避免調用 \Log
        $cloverTg->onError(function ($e, $context) {});

        $result = $cloverTg->send('Test message');
        
        $this->assertNull($result);
        $this->assertFalse($cloverTg->isSuccess());
        $this->assertNotNull($cloverTg->getLastError());
    }

    /**
     * @test
     */
    public function it_can_get_last_error()
    {
        $this->fakeErrorResponse(['error' => 'Bad Request'], 400);

        $cloverTg = $this->createCloverTg();
        // 設置自定義錯誤處理器以避免調用 \Log
        $cloverTg->onError(function ($e, $context) {});

        $cloverTg->send('Test message');
        $error = $cloverTg->getLastError();
        
        $this->assertNotNull($error);
        $this->assertArrayHasKey('message', $error);
        $this->assertArrayHasKey('code', $error);
        $this->assertArrayHasKey('context', $error);
    }

    /**
     * @test
     */
    public function it_can_get_last_response()
    {
        $this->fakeSuccessResponse(['data' => ['message_id' => 123]]);

        $cloverTg = $this->createCloverTg();
        $cloverTg->send('Test message');
        $response = $cloverTg->getLastResponse();
        
        $this->assertNotNull($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals(200, $response['status']);
    }

    /**
     * @test
     */
    public function it_can_clear_state()
    {
        $this->fakeSuccessResponse(['data' => 'ok']);

        $cloverTg = $this->createCloverTg();
        $cloverTg->send('Test message');
        $this->assertNotNull($cloverTg->getLastResponse());
        
        $cloverTg->clearState();
        $this->assertNull($cloverTg->getLastResponse());
        $this->assertNull($cloverTg->getLastError());
    }

    /**
     * @test
     */
    public function it_can_set_custom_error_handler()
    {
        $this->fakeErrorResponse(['error' => 'Bad Request'], 400);

        $errorHandlerCalled = false;
        $capturedError = null;

        $cloverTg = $this->createCloverTg();
        $cloverTg->onError(function ($e, $context) use (&$errorHandlerCalled, &$capturedError) {
            $errorHandlerCalled = true;
            $capturedError = $context;
        });

        $cloverTg->send('Test message');
        
        $this->assertTrue($errorHandlerCalled);
        $this->assertNotNull($capturedError);
    }

    // ==================== isSuccess 測試 ====================

    /**
     * @test
     */
    public function it_returns_true_for_success()
    {
        $this->fakeSuccessResponse(['data' => 'ok']);

        $cloverTg = $this->createCloverTg();
        $cloverTg->send('Test');
        
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_returns_false_for_failure()
    {
        $this->fakeErrorResponse(['error' => 'Server Error'], 500);

        $cloverTg = $this->createCloverTg();
        // 設置自定義錯誤處理器以避免調用 \Log
        $cloverTg->onError(function ($e, $context) {});

        $cloverTg->send('Test');
        
        $this->assertFalse($cloverTg->isSuccess());
    }
}
