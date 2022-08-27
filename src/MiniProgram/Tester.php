<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform\MiniProgram;

use XinFox\WechatPlatform\AbstractApi;
use XinFox\WechatPlatform\Exception\InvalidArgumentException;
use XinFox\WechatPlatform\HttpClient;

class Tester extends AbstractApi
{
    /**
     * 绑定体验者
     * @param string $authorizerAppId
     * @param string $wechatId
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function bind(string $authorizerAppId, string $wechatId)
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "/wxa/unbind_tester?access_token={$token}";
        $data = [
            "wechatid" => $wechatId
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 解除绑定体验者
     * @param string $authorizerAppId
     * @param string $id
     * @param string $idType
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     * @throws \XinFox\WechatPlatform\Exception\InvalidArgumentException
     */
    public function unbind(string $authorizerAppId, string $id, string $idType = 'wechatid')
    {
        if (!in_array($idType, ['wechatid', 'userstr'])) {
            throw new InvalidArgumentException("idType参数错误");
        }
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "/wxa/unbind_tester?access_token={$token}";
        $data = [
            $idType => $id
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 获取体验者列表
     * @param string $authorizerAppId
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function list(string $authorizerAppId)
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "/wxa/memberauth?access_token={$token}";
        $data = [
            "action" => "get_experiencer"
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }
}