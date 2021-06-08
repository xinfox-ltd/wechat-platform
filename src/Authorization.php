<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform;

/**
 * 参数	类型	说明
 * authorizer_appid	string	授权方 appid
 * authorizer_access_token	string	接口调用令牌（在授权的公众号/小程序具备 API 权限时，才有此返回值）
 * expires_in	number	authorizer_access_token 的有效期（在授权的公众号/小程序具备API权限时，才有此返回值），单位：秒
 * authorizer_refresh_token	string	刷新令牌（在授权的公众号具备API权限时，才有此返回值），刷新令牌主要用于第三方平台获取和刷新已授权用户的 authorizer_access_token。一旦丢失，只能让用户重新授权，才能再次拿到新的刷新令牌。用户重新授权后，之前的刷新令牌会失效
 * func_info 授权给开发者的权限集列表
 * Class Authorization
 * @package XinFox\WechatPlatform
 */
class Authorization
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getAppId(): string
    {
        return $this->data['authorizer_appid'];
    }

    public function getAccessToken(): string
    {
        return $this->data['authorizer_access_token'];
    }

    public function getRefreshToken(): string
    {
        return $this->data['authorizer_refresh_token'];
    }

    /**
     * 获取过期时间（时间戳）
     * @return int
     */
    public function getExpireTime(): int
    {
        return $this->data['expire_time'];
    }

    public function getFuncInfo(): array
    {
        return $this->data['func_info'];
    }

    public function __toString()
    {
        return $this->getAccessToken();
    }
}