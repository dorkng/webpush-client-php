<?php

namespace Dork\Webpush;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Subscriber
{
    /**
     * @var string
     */
    protected $uid;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     * @param string $uid
     */
    public function __construct(Client $client, string $uid)
    {
        $this->uid = $uid;
        $this->client = $client;
    }

     /**
     * Register the device to list of subscribers.
     *
     * @param string $endpoint
     * @param string|null $publicKey
     * @param string|null $authToken
     * @param string|null $contentEncoding
     * @return mixed
     */
    public function subscribe($endpoint, $publicKey = null, $authToken = null)
    {
        $params = [
            'endpoint' => $endpoint,
            'publicKey' => $publicKey,
            'authToken' => $authToken,
            'uid' => (string)$this->uid,
        ];

        $response = $this->call('POST', '/subscriptions', [RequestOptions::JSON => $params]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Unregister the device from list of subscribers.
     *
     * @param string $subscriptionId
     * @return void
     */
    public function unsubscribe($subscriptionId = null)
    {
        $params = ['uid' => $this->uid, 'subscriptionId' => $subscriptionId,];
        $this->call('DELETE', '/subscriptions', [RequestOptions::JSON => $params]);
    }

    /**
     * Notify the subscriber.
     *
     * @param array $args
     * @return void
     */
    public function notify($args = [])
    {
        $this->call('POST', '/notify/' . $this->uid, [RequestOptions::JSON => $args]);
    }

    /**
     * Make an api call to the webpush server
     *
     * @param string $method
     * @param string $url
     * @param array $params
     * @return \PSR\Http\Message\ResponseInterface
     */
    protected function call($method, $url, $params = [])
    {
        return $this->client->request($method, $url, $params);
    }
}
