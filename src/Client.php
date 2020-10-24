<?php

namespace Dork\Webpush;

use GuzzleHttp\Client as HttpClient;
use InvalidArgumentException;

class Client
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @param string $url
     * @param string $token
     * @return void
     */
    public function __construct($url, $token)
    {
        if (empty($url) || empty($token)) {
            throw new InvalidArgumentException('WebPush server url and token is required');
        }
        $this->client = new HttpClient([
            'base_uri' => $url,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
    }

    /**
     * Get the subscriber instance.
     *
     * @param string $uid
     * @return Subscriber
     */
    public function for($uid)
    {
        return new Subscriber($this->client, $uid);
    }
}
