<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform\Api;

use Psr\SimpleCache\InvalidArgumentException;
use XinFox\WechatPlatform\Authorization;
use XinFox\WechatPlatform\Authorizer;
use XinFox\WechatPlatform\Exception\AuthorizationNotExistException;
use XinFox\WechatPlatform\Exception\ComponentVerifyTicketException;
use XinFox\WechatPlatform\Http;
use XinFox\WechatPlatform\ThirdPartyPlatform;

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

    private ThirdPartyPlatform $platform;

    public function __construct(ThirdPartyPlatform $partyPlatform)
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

    /**
     * 获取令牌
     * 获取第三方平台component_access_token
     *
     * @param string|null $componentVerifyTicket
     * @return string
     * @throws InvalidArgumentException|ComponentVerifyTicketException
     */
    public function getComponentAccessToken(string $componentVerifyTicket = null): string
    {
        if ($this->platform->getCache()->has('component_access_token')) {
            return $this->platform->getCache()->get('component_access_token');
        } else {
            $componentVerifyTicket = $componentVerifyTicket ?? $this->getComponentVerifyTicket();

            $data = [
                'component_appid' => $this->platform->getConfig()->getAppId(),
                'component_appsecret' => $this->platform->getConfig()->getAppSecret(),
                'component_verify_ticket' => $componentVerifyTicket,
            ];

            $json = Http::getInstance()
                ->post(self::BASE_URI . self::API_COMPONENT_TOKEN, $data);

            $this->platform->getCache()->set(
                'component_access_token',
                $json['component_access_token'],
                $json['expires_in'] - 10
            );

            return $json['component_access_token'];
        }
    }

    /**
     * 获取预授权码pre_auth_code
     *
     * @param string|null $componentAccessToken
     * @return mixed
     * @throws InvalidArgumentException|ComponentVerifyTicketException
     */
    public function getPreAuthCode(string $componentAccessToken = null)
    {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();
        $data = [
            'component_appid' => $this->platform->getConfig()->getAppId(),
        ];

        $uri = self::BASE_URI . self::API_CREATE_PREAUTHCODE . "?component_access_token=" . $componentAccessToken;
        $json = Http::getInstance()
            ->post($uri, $data);

        return $json['pre_auth_code'];
    }

    /**
     * 通过授权码和自己的接口调用凭据（component_access_token），
     * 换取公众号或小程序的接口调用凭据（authorizer_access_token 和用于前者快过期时用来刷新它的 authorizer_refresh_token）和授权信息（授权了哪些权限等信息）
     *
     * @param string $authorizationCode 授权code,会在授权成功时返回给第三方平台，详见第三方平台授权流程说明，不是预授权码
     * @param string|null $componentAccessToken 第三方平台component_access_token，不是authorizer_access_token，通过接口 getComponentAccessToken获取
     * @return Authorization
     * @throws InvalidArgumentException
     * @throws ComponentVerifyTicketException
     */
    public function apiQueryAuth(string $authorizationCode, string $componentAccessToken = null): Authorization
    {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();
        $data = [
            'component_appid' => $this->platform->getConfig()->getAppId(),
            'authorization_code' => $authorizationCode,
        ];

        $uri = self::BASE_URI . self::API_QUERY_AUTH . "?component_access_token={$componentAccessToken}";
        $response = Http::getInstance()
            ->post($uri, $data);

        $authorization = new Authorization($response);
        $this->platform->getAuthorizationRepository()->save($authorization);

        return $authorization;
    }

    /**
     * @param string $value
     * @throws InvalidArgumentException
     */
    public function setComponentVerifyTicket(string $value)
    {
        $this->platform->getCache()->set("component_verify_ticket", $value, 86400);
    }

    /**
     * 获取微信后台推送的ticket
     *
     * @return mixed
     * @throws InvalidArgumentException|ComponentVerifyTicketException
     */
    public function getComponentVerifyTicket(): string
    {
        $ticket = $this->platform->getCache()->get('component_verify_ticket');
        if (empty($ticket)) {
            throw new ComponentVerifyTicketException();
        }

        return $ticket;
    }

    /**
     * 获取授权方access_token
     *
     * @param string $authorizerAppId
     * @return mixed
     * @throws InvalidArgumentException|AuthorizationNotExistException
     * @throws ComponentVerifyTicketException
     */
    public function getAuthorizerAccessToken(string $authorizerAppId): Authorization
    {
        $authorization = $this->platform->getAuthorizationRepository()->getOneByAuthorizerAppId($authorizerAppId);
        if (!$authorization) {
            throw new AuthorizationNotExistException();
        }

        $time = time();
        if ($authorization->getExpireTime() < $time || $authorization->getExpireTime() - $time < 60) {
            // 刷新token
            $authorization = $this->refreshAuthorizerToken($authorizerAppId, $authorization->getRefreshToken());
            $this->platform->getAuthorizationRepository()->save($authorization);
        }

        return $authorization;
    }


    /**
     * 获取（刷新）授权公众号或小程序的接口调用凭据（令牌）
     *
     * @param string $authorizerAppId
     * @param string $authorizerRefreshToken
     * @param string|null $componentAccessToken
     * @return Authorization
     * @throws InvalidArgumentException|ComponentVerifyTicketException
     */
    public function refreshAuthorizerToken(
        string $authorizerAppId,
        string $authorizerRefreshToken,
        string $componentAccessToken = null
    ): Authorization {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();

        $data = [
            'component_appid' => $this->platform->getConfig()->getAppId(),
            'authorizer_appid' => $authorizerAppId,
            'authorizer_refresh_token' => $authorizerRefreshToken,
        ];

        $uri = self::BASE_URI . self::API_AUTHORIZER_TOKEN . "?component_access_token={$componentAccessToken}";
        $authorization = new Authorization(
            Http::getInstance()
                ->post($uri, $data)
        );
        $this->platform->getAuthorizationRepository()->save($authorization);

        return $authorization;
    }

    /**
     * 获取授权方的帐号基本信息
     *
     * @param string $authorizerAppId
     * @param string|null $componentAccessToken
     * @return Authorizer
     * @throws InvalidArgumentException
     * @throws ComponentVerifyTicketException
     */
    public function getAuthorizerInfo(string $authorizerAppId, string $componentAccessToken = null): Authorizer
    {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();

        $data = [
            'component_appid' => $this->platform->getConfig()->getAppId(),
            'authorizer_appid' => $authorizerAppId,
        ];

        $uri = self::BASE_URI . self::API_GET_AUTHORIZER_INFO . "?component_access_token=" . $componentAccessToken;
        $authorizer = new Authorizer(
            Http::getInstance()
                ->post($uri, $data)
        );

        $this->platform->getAuthorizerRepository()->save($authorizer);
        return $authorizer;
    }

    /**
     * 获取授权方的选项设置信息
     *
     * @param string $authorizerAppId
     * @param string $optionName
     * @param string|null $componentAccessToken
     * @return array
     * @throws InvalidArgumentException
     * @throws ComponentVerifyTicketException
     */
    public function getAuthorizerOption(
        string $authorizerAppId,
        string $optionName,
        string $componentAccessToken = null
    ): array {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();

        $data = [
            'component_appid' => $this->platform->getConfig()->getAppId(),
            'authorizer_appid' => $authorizerAppId,
            'option_name' => $optionName
        ];

        $uri = self::BASE_URI . self::API_GET_AUTHORIZER_OPTION . "?component_access_token=" . $componentAccessToken;
        return Http::getInstance()
            ->post($uri, $data);
    }

    /**
     * 获取授权方的选项设置信息
     *
     * location_report（地理位置上报选项）{0无上报 1进入会话时上报 2每5s上报
     * voice_recognize（语音识别开关选项）{0关闭语音识别 1开启语音识别
     * customer_service（多客服开关选项）{0关闭多客服 1开启多客服
     *
     * @param string $authorizerAppId
     * @param string $optionName
     * @param string $optionValue
     * @param string|null $componentAccessToken
     * @return array
     * @throws InvalidArgumentException
     * @throws ComponentVerifyTicketException
     */
    public function setAuthorizerOption(
        string $authorizerAppId,
        string $optionName,
        string $optionValue,
        string $componentAccessToken = null
    ): array {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();

        $data = [
            'component_appid' => $this->platform->getConfig()->getAppId(),
            'authorizer_appid' => $authorizerAppId,
            'option_name' => $optionName,
            'option_value' => $optionValue
        ];

        $uri = self::BASE_URI . self::API_SET_AUTHORIZER_OPTION . "?component_access_token=" . $componentAccessToken;
        return Http::getInstance()
            ->post($uri, $data);
    }
}