<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform;

use http\Exception\InvalidArgumentException;

class Config
{
    private string $appId;

    private string $appSecret;

    public function __construct(array $config)
    {
        if (empty($config['app_id']) || empty($config['app_secret'])) {
            throw new \XinFox\Exception\InvalidArgumentException('缺少参数');
        }

        $this->appId = $config['app_id'];
        $this->appSecret = $config['app_secret'];
    }

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * @return string
     */
    public function getAppSecret(): string
    {
        return $this->appSecret;
    }
}