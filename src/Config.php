<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform;

use XinFox\WechatPlatform\Exception\InvalidArgumentException;

class Config
{
    private array $config;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(array $config)
    {
        if (empty($config['app_id']) || empty($config['app_secret']) || empty($config['token']) || empty($config['encrypt_key'])) {
            throw new InvalidArgumentException('缺少参数');
        }

        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return $this->config['app_id'];
    }

    /**
     * @return string
     */
    public function getAppSecret(): string
    {
        return $this->config['app_secret'];
    }

    public function getToken(): string
    {
        return $this->config['token'];
    }

    public function getEncryptKey(): string
    {
        return $this->config['encrypt_key'];
    }
}