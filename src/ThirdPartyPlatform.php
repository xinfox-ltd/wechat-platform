<?php

namespace XinFox\WechatPlatform;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use XinFox\WechatPlatform\Api\ThirdPartyPlatform\Oauth2;
use XinFox\WechatPlatform\Exception\AuthorizationNotExistException;
use XinFox\WechatPlatform\Exception\ComponentVerifyTicketException;

class ThirdPartyPlatform
{
    private Config $config;

    private CacheInterface $cache;

    private AuthorizationRepository $authorizationRepository;

    private AuthorizerRepository $authorizerRepository;

    public function __construct(Config $config, CacheInterface $cache, AuthorizationRepository $authorizationRepository, AuthorizerRepository $authorizerRepository)
    {
        $this->config = $config;
        $this->cache = $cache;
        $this->authorizationRepository = $authorizationRepository;
        $this->authorizerRepository = $authorizerRepository;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return CacheInterface
     */
    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * @return AuthorizationRepository
     */
    public function getAuthorizationRepository(): AuthorizationRepository
    {
        return $this->authorizationRepository;
    }

    /**
     * @return AuthorizerRepository
     */
    public function getAuthorizerRepository(): AuthorizerRepository
    {
        return $this->authorizerRepository;
    }
}