<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform\MiniProgram;

use XinFox\WechatPlatform\AbstractApi;
use XinFox\WechatPlatform\Exception\InvalidArgumentException;
use XinFox\WechatPlatform\HttpClient;

class Basics extends AbstractApi
{
    /**
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException|\XinFox\WechatPlatform\Exception\InvalidArgumentException
     */
    public function modifyDomain(
        array $requestDomain,
        array $wsRequestDomain,
        array $uploadDomain,
        array $downloadDomain,
        string $action = 'add'
    ) {
        if (!in_array($action, ['add', 'delete', 'set', 'get',])) {
            throw new InvalidArgumentException();
        }

        $componentAccessToken = $this->platform->getComponentAccessToken();;
        $api = "https://api.weixin.qq.com/wxa/modify_domain?access_token={$componentAccessToken}";
        $data = [
            "action" => $action,
            "requestdomain" => $requestDomain,
            "wsrequestdomain" => $wsRequestDomain,
            "uploaddomain" => $uploadDomain,
            "downloaddomain" => $downloadDomain
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }
}