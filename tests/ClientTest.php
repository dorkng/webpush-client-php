<?php

namespace Tests;

use Dork\Webpush\Client;
use Dork\Webpush\Subscriber;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase as BaseTestCase;
use stdClass;

class ClientTest extends BaseTestCase
{
    /**
     * @var GuzzleHttpClient&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $httpClient;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = $this->createMock(GuzzleHttpClient::class);
    }

    /**
     * This tests that an exception is thrown when parameters (url & token) are missing.
     *
     * @return void
     */
    public function testInitializationException()
    {
        $this->expectException(InvalidArgumentException::class);
        new Client(null, null);
    }

    /**
     * @return void
     */
    public function testInitialize(): void
    {
        $client = new Client('stub', 'stub');

        $this->assertInstanceOf(Subscriber::class, $client->for('testid'));
    }

    /**
     * @return void
     */
    public function testSubscribe()
    {
        $client = $this->webPushClient();
        $params = [
            RequestOptions::JSON => [
                'uid' => 'uid:90',
                'authToken' => 'token',
                'publicKey' => 'secret',
                'endpoint' => 'http://example.com',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with($this->equalTo('POST'), $this->equalTo('/subscriptions'), $this->equalTo($params))
            ->willReturn(new Response(200, [], '{"subscriptionId": "abc"}'));

        $response = $client->for('uid:90')->subscribe('http://example.com', 'secret', 'token');
        $this->assertSame(['subscriptionId' => 'abc'], $response);
    }

    /**
     * @return void
     */
    public function testUnsubscribe()
    {
        $client = $this->webPushClient();
        $params = [
            RequestOptions::JSON => [
                'uid' => 'uid:90',
                'subscriptionId' => 'xyzArw',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with($this->equalTo('DELETE'), $this->equalTo('/subscriptions'), $params)
            ->willReturn(new Response());

        $client->for('uid:90')->unsubscribe('xyzArw');
    }

    /**
     * @return void
     */
    public function testNotifyUser()
    {
        $data = ['title' => 'Hello'];
        $params = [RequestOptions::JSON => $data];

        $client = $this->webPushClient();

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with($this->equalTo('POST'), $this->equalTo('/notify/uid:90'), $this->equalTo($params))
            ->willReturn(new Response());

        $client->for('uid:90')->notify($data);
    }

    /**
     * @return void
     */
    public function testHttpRequestFails()
    {
        $this->expectException(GuzzleException::class);

        $client = new Client('http://webpushserver.test/api', 'secret');
        $client->for('uid:90')->subscribe('http://example.com');
    }

    /**
     * Make a stub client.
     *
     * @return \Dork\Webpush\Client&\PHPUnit\Framework\MockObject\Stub
     */
    protected function webPushClient()
    {
        $client = $this->createStub(Client::class);

        $client->method('for')
            ->will($this->returnCallback(fn($uid) => new Subscriber($this->httpClient, $uid)));

        return $client;
    }
}
