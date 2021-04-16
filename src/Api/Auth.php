<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform\Api;

use GuzzleHttp\Exception\GuzzleException;
use Psr\SimpleCache\InvalidArgumentException;
use XinFox\WechatPlatform\Authorization;
use XinFox\WechatPlatform\Authorizer;
use XinFox\WechatPlatform\Exception\ApiException;
use XinFox\WechatPlatform\Exception\AuthorizationNotExistException;
use XinFox\WechatPlatform\Exception\ComponentVerifyTicketException;
use XinFox\WechatPlatform\HttpClient;
use XinFox\WechatPlatform\WechatPlatform;

class Auth
{
    const API_QUERY_AUTH = "/component/api_query_auth";
    const BASE_URI = 'https://api.weixin.qq.com/cgi-bin';
    const API_COMPONENT_TOKEN = "/component/api_component_token";
    const API_CREATE_PREAUTHCODE = "/component/api_create_preauthcode";
    const API_AUTHORIZER_TOKEN = '/component/api_authorizer_token';
    const API_GET_AUTHORIZER_INFO = '/component/api_get_authorizer_info';
    const API_GET_AUTHORIZER_OPTION = '/component/api_get_authorizer_option';
    const API_SET_AUTHORIZER_OPTION = '/component/api_set_authorizer_option';

    private WechatPlatform $platform;

    public function __construct(WechatPlatform $partyPlatform)
    {
        $this->platform = $partyPlatform;
    }

    /**
     * 生成授权url
     *
     * @param string $preAuthCode 预授权码
     * @param string $redirectUri 回调 URI
     * @param string $mode 授权方式：wap 点击移动端链接快速授权, scan 授权注册页面扫码授权
     * @param string $bizAppId 指定授权唯一的小程序或公众号
     * @return string
     */
    public function generateAuthUrl(
        string $preAuthCode,
        string $redirectUri,
        $mode = 'wap',
        string $bizAppId = ''
    ): string {
        $redirectUri = urlencode($redirectUri);
        if ($mode == 'wap') {
            return $this->generateLinkAuthUrl($preAuthCode, $redirectUri, $bizAppId);
        } else {
            return $this->generateScanAuthUrl($preAuthCode, $redirectUri, $bizAppId);
        }
    }

    /**
     * 生成移动端快速授权链接
     *
     * @param string $preAuthCode
     * @param string $redirectUri
     * @param string $bizAppId
     * @return string
     */
    public function generateLinkAuthUrl(string $preAuthCode, string $redirectUri, string $bizAppId = ''): string
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=%s'
            . '&pre_auth_code=%s&redirect_uri=%s&auth_type=3&biz_appid=%s';
        return sprintf(
            $url,
            $this->platform->getConfig()->getAppId(),
            $preAuthCode,
            $redirectUri,
            $bizAppId
        );
    }

    /**
     * 生成授权扫码授权注册页面链接
     *
     * @param string $preAuthCode
     * @param string $redirectUri
     * @param string $bizAppId
     * @return string
     */
    public function generateScanAuthUrl(string $preAuthCode, string $redirectUri, string $bizAppId = ''): string
    {
        $url = 'https://mp.weixin.qq.com/safe/bindcomponent?action=bindcomponent&no_scan=1&component_appid=%s'
            . '&pre_auth_code=%s&redirect_uri=%s&auth_type=3&biz_appid=%s#wechat_redirect';
        return sprintf(
            $url,
            $this->platform->getConfig()->getAppId(),
            $preAuthCode,
            $redirectUri,
            $bizAppId
        );
    }


}