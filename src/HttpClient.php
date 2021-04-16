<?php

namespace XinFox\WechatPlatform;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\StreamInterface;
use XinFox\WechatPlatform\Exception\ApiException;

class HttpClient
{
    protected static HttpClient $instance;

    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(
            [
                'base_uri' => 'https://api.weixin.qq.com',
                'timeout' => 2.0,
            ]
        );
    }

    public static function getInstance(): HttpClient
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param $uri
     * @param $data
     * @return array
     * @throws ApiException|GuzzleException
     */
    public function post($uri, $data): array
    {
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
        $body = $this->client
            ->post($uri, ['body' => $data])
            ->getBody();

        return $this->getRequestContents($uri, $body);
    }

    /**
     * @param $uri
     * @param array|null $data
     * @return array
     * @throws ApiException
     * @throws GuzzleException
     */
    public function get($uri, array $data = null): array
    {
        $options = $data ? ['query' => $data] : [];
        $body = $this->client
            ->get($uri, $options)
            ->getBody();

        return $this->getRequestContents($uri, $body);
    }

    /**
     * @param string $uri
     * @param StreamInterface $stream
     * @return array
     * @throws ApiException
     */
    public function getRequestContents(string $uri, StreamInterface $stream): array
    {
        $json = json_decode($stream->getContents(), true);
        if (isset($json['errcode']) && $json['errcode'] <> 0) {
            throw new ApiException(
                sprintf("%s 获取失败：[%s][%s]", $uri, $json['errcode'], $json['errmsg'])
            );
        }

        return $json;
    }
}