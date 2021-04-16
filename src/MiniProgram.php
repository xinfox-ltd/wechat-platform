<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform;

use XinFox\WechatPlatform\Exception\InvalidArgumentException;
use XinFox\WechatPlatform\MiniProgram\Auth;

/**
 * Class MiniProgram
 * @property Auth $auth
 * @package XinFox\WechatPlatform
 */
class MiniProgram extends AbstractApi
{
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $name)
    {
        $class = 'XinFox\\WechatPlatform\\MiniProgram\\' . $name;
        if (class_exists($class)) {
            throw new InvalidArgumentException("$class not exists");
        }

        return new $class($this->platform);
    }
}