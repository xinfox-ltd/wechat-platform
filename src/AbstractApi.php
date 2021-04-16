<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform;

abstract class AbstractApi
{
    protected WechatPlatform $platform;

    public function __construct(WechatPlatform $platform)
    {
        $this->platform = $platform;
    }
}