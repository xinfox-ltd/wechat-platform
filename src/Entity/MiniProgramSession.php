<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform\Entity;

/**
 * 小程序登录返回session信息
 * Class MiniProgramSession
 * @package XinFox\Module\User\Infrastructure\Wechat
 */
class MiniProgramSession
{
    private ?string $unionId;

    private string $openId;

    private string $sessionKey;

    public function __construct(string $openId, string $sessionKey, ?string $unionId)
    {
        $this->unionId = $unionId;
        $this->openId = $openId;
        $this->sessionKey = $sessionKey;
    }

    public function getUnionId(): ?string
    {
        return $this->unionId;
    }

    public function getOpenId(): string
    {
        return $this->openId;
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }
}