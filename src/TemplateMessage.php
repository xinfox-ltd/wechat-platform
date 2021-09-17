<?php

namespace XinFox\WechatPlatform;

use XinFox\WechatPlatform\AbstractApi;
use XinFox\WechatPlatform\HttpClient;
use XinFox\WechatPlatform\TemplateMessages\TemplateMessageInterface;

class TemplateMessage extends AbstractApi
{

    public function send(TemplateMessageInterface $message)
    {
        $token = $this->platform->getAuthorizerAccessToken($message->getAppId());
        $uri = '/cgi-bin/message/template/send?access_token=' . $token;

        return HttpClient::getInstance()->post($uri, $message->toArray());
    }
}
