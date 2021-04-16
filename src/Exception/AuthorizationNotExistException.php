<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform\Exception;

use Throwable;

class AuthorizationNotExistException extends WechatPlatformException
{
    public function __construct($message = "微信授权信息不存在，请重新授权", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}