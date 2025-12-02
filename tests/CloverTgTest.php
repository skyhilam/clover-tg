<?php

namespace Clover\CloverTg\Tests;

use Clover\CloverTg\CloverTg;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class CloverTgTest extends TestCase
{
    protected $cloverTg;
    protected $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cloverTg = $this->createCloverTgWithMock();
    }

    /**
     * 創建帶有 Mock HTTP 客戶端的 CloverTg 實例
     */
    protected function createCloverTgWithMock($responses = [])
    {
        if (empty($responses)) {
            $responses = [
                new Response(200, [], json_encode(['data' => 'ok']))
            ];
        }

        $this->mockHandler = new MockHandler($responses);
        $handlerStack = HandlerStack::create($this->mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        // 創建 CloverTg 實例並注入 mock client
        $cloverTg = new class extends CloverTg {
            public function __construct()
            {
                // 不調用父構造函數，避免依賴 Laravel config
                $this->token = 'test-token';
            }
            
            public function setMockClient($client)
            {
                $this->client = $client;
            }
            
            // 覆蓋 getToken 方法以避免調用 Laravel config()
            protected function getToken(): string
            {
                return $this->token ?? 'test-token';
            }
        };
        
        $cloverTg->setMockClient($mockClient);

        return $cloverTg;
    }

    // ==================== 基本功能測試 ====================

    /**
     * @test
     */
    public function it_can_set_token()
    {
        $result = $this->cloverTg->token('custom-token');
        
        $this->assertSame($this->cloverTg, $result);
    }

    /**
     * @test
     */
    public function it_can_set_message()
    {
        $result = $this->cloverTg->message('Test message');
        
        $this->assertSame($this->cloverTg, $result);
    }

    /**
     * @test
     */
    public function it_can_set_message_id()
    {
        $result = $this->cloverTg->messageId(12345);
        
        $this->assertSame($this->cloverTg, $result);
    }

    /**
     * @test
     */
    public function it_can_set_callback()
    {
        $result = $this->cloverTg->callback('https://example.com/callback');
        
        $this->assertSame($this->cloverTg, $result);
    }

    /**
     * @test
     */
    public function it_can_set_ex_time()
    {
        $result = $this->cloverTg->exTime(120);
        
        $this->assertSame($this->cloverTg, $result);
    }

    /**
     * @test
     */
    public function it_can_set_options()
    {
        $result = $this->cloverTg->options(['key' => 'value']);
        
        $this->assertSame($this->cloverTg, $result);
    }

    /**
     * @test
     */
    public function it_can_set_buttons()
    {
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
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => ['message_id' => 123]]))
        ]);

        $result = $cloverTg->send('Hello World!');
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_can_notify()
    {
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => 'ok']))
        ]);

        $result = $cloverTg->message('Test notification')->notify();
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_can_dispatch()
    {
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => 'ok']))
        ]);

        $result = $cloverTg->message('Test dispatch')->dispatch();
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_can_send_with_callback()
    {
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => ['message_id' => 123]]))
        ]);

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
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => ['message_id' => 123]]))
        ]);

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
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => ['message_id' => 123]]))
        ]);

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
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => ['message_id' => 123]]))
        ]);

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
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => ['message_id' => 123]]))
        ]);

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
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => true]))
        ]);

        $result = $cloverTg->edit(12345, 'Updated message');
        
        $this->assertNotNull($result);
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_can_edit_caption()
    {
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => true]))
        ]);

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
        $request = new Request('POST', '/send');
        $response = new Response(400, [], json_encode(['error' => 'Bad Request']));
        
        $cloverTg = $this->createCloverTgWithMock([
            new ClientException('Bad Request', $request, $response)
        ]);

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
        $request = new Request('POST', '/send');
        $response = new Response(400, [], json_encode(['error' => 'Bad Request']));
        
        $cloverTg = $this->createCloverTgWithMock([
            new ClientException('Bad Request', $request, $response)
        ]);

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
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => ['message_id' => 123]]))
        ]);

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
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => 'ok']))
        ]);

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
        $errorHandlerCalled = false;
        $capturedError = null;
        
        $request = new Request('POST', '/send');
        $response = new Response(400, [], json_encode(['error' => 'Bad Request']));
        
        $cloverTg = $this->createCloverTgWithMock([
            new ClientException('Bad Request', $request, $response)
        ]);

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
        $cloverTg = $this->createCloverTgWithMock([
            new Response(200, [], json_encode(['data' => 'ok']))
        ]);

        $cloverTg->send('Test');
        
        $this->assertTrue($cloverTg->isSuccess());
    }

    /**
     * @test
     */
    public function it_returns_false_for_failure()
    {
        $request = new Request('POST', '/send');
        $response = new Response(500, [], json_encode(['error' => 'Server Error']));
        
        $cloverTg = $this->createCloverTgWithMock([
            new ClientException('Server Error', $request, $response)
        ]);

        // 設置自定義錯誤處理器以避免調用 \Log
        $cloverTg->onError(function ($e, $context) {});

        $cloverTg->send('Test');
        
        $this->assertFalse($cloverTg->isSuccess());
    }
}
