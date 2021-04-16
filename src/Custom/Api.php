<?php

namespace XinFox\WechatPlatform\Custom;

use XinFox\WechatPlatform\Api\ThirdPartyPlatform;
use XinFox\WechatPlatform\Custom\Message\MessageInterface;
use XinFox\WechatPlatform\Exception;
use XinFox\WechatPlatform\HttpClient;

class Api
{
    protected $authorizerAppid;

    /**
     * Api constructor.
     * @param string $authorizerAppid 授权公众号appid
     */
    public function __construct(string $authorizerAppid)
    {
        $this->authorizerAppid = $authorizerAppid;
    }

    /**
     * @param MessageInterface $message
     * @param string $authorizerAccessToken
     * @return array
     * @throws Exception
     */
    public function sendMessage(MessageInterface $message, string $authorizerAccessToken = null)
    {
        $accessToken = $authorizerAccessToken ?? ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($this->authorizerAppid);

        $uri = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$accessToken}";
        return HttpClient::getInstance()
            ->post($uri, $message->toArray());
    }
}