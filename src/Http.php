<?php

namespace XinFox\WechatPlatform;

use GuzzleHttp\Client;

class Http
{
    protected static $instance;

    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public static function getInstance()
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
     * @throws Exception
     */
    public function post($uri, $data): ?array
    {
        $body = $this->client
            ->post($uri, ['body' => is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data])
            ->getBody();

        $json = json_decode($body->getContents(), true);
        if (isset($json['errcode']) && $json['errcode'] <> 0) {
            throw new Exception(
                sprintf("%s 获取失败：[%s][%s]", $uri, $json['errcode'], $json['errmsg'])
            );
        }

        return $json;
    }

    /**
     * @param $uri
     * @return array
     * @throws Exception
     */
    public function get($uri): array
    {
        $body = $this->client
            ->get($uri)
            ->getBody();

        $json = json_decode($body->getContents(), true);
        if (isset($json['errcode']) && $json['errcode'] <> 0) {
            throw new Exception(
                sprintf("%s 获取失败：[%s][%s]", $uri, $json['errcode'], $json['errmsg'])
            );
        }

        return $json;
    }
}