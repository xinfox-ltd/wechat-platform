<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform;

/**
 * 开发平台接口
 * @package XinFox\WechatPlatform
 */
class OpenPlatform extends AbstractApi
{
    /**
     * 创建开放平台帐号并绑定公众号/小程序
     *
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     */
    public function create(string $authorizerAppId)
    {
        $accessToken = $this->platform->getAuthorizerAccessToken($authorizerAppId);

        return HttpClient::getInstance()->post(
            "https://api.weixin.qq.com/cgi-bin/open/create?access_token=$accessToken",
            [
                'appid' => $authorizerAppId
            ]
        );
    }

    /**
     * 将公众号/小程序绑定到开放平台帐号下
     *
     * @param string $openAppid
     * @param string $authorizerAppId
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function bind(string $openAppid, string $authorizerAppId)
    {
        $accessToken = $this->platform->getAuthorizerAccessToken($authorizerAppId);

        return HttpClient::getInstance()->post(
            "https://api.weixin.qq.com/cgi-bin/open/bind?access_token=$accessToken",
            [
                'appid' => $authorizerAppId,
                'open_appid' => $openAppid
            ]
        );
    }

    /**
     * 将公众号/小程序从开放平台帐号下解绑
     *
     * @param string $openAppid
     * @param string $authorizerAppId
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function unbind(string $openAppid, string $authorizerAppId)
    {
        $accessToken = $this->platform->getAuthorizerAccessToken($authorizerAppId);

        return HttpClient::getInstance()->post(
            "https://api.weixin.qq.com/cgi-bin/open/unbind?access_token=$accessToken",
            [
                'appid' => $authorizerAppId,
                'open_appid' => $openAppid
            ]
        );
    }

    /**
     * 获取公众号/小程序所绑定的开放平台帐号
     *
     * @param string $authorizerAppId
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function get(string $authorizerAppId)
    {
        $accessToken = $this->platform->getAuthorizerAccessToken($authorizerAppId);

        return HttpClient::getInstance()->post(
            "https://api.weixin.qq.com/cgi-bin/open/get?access_token=$accessToken",
            [
                'appid' => $authorizerAppId,
            ]
        );
    }
}