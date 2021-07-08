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
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     */
    public function modifyDomain(
        string $authorizerAppId,
        array $requestDomain,
        array $wsRequestDomain,
        array $uploadDomain,
        array $downloadDomain,
        string $action = 'add'
    ) {
        if (!in_array($action, ['add', 'delete', 'set', 'get',])) {
            throw new InvalidArgumentException();
        }

        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/modify_domain?access_token={$token}";

        foreach ($requestDomain as &$url) {
            $url = trim($url, '/');
        }

        foreach ($wsRequestDomain as &$url) {
            $url = trim($url, '/');
        }

        foreach ($uploadDomain as &$url) {
            $url = trim($url, '/');
        }

        foreach ($downloadDomain as &$url) {
            $url = trim($url, '/');
        }

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

    /**
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     * @throws \XinFox\WechatPlatform\Exception\InvalidArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     */
    public function setWebViewDomain($authorizerAppId, array $webViewDomain, string $action = 'add')
    {
        if (!in_array($action, ['add', 'delete', 'set', 'get',])) {
            throw new InvalidArgumentException();
        }

        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/setwebviewdomain?access_token={$token}";

        foreach ($webViewDomain as &$url) {
            $url = trim($url, '/');
        }

        $data = [
            "action" => $action,
            "webviewdomain" => $webViewDomain,
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }
}