<?php

namespace XinFox\WechatPlatform;

interface MessageInterface
{
    public function setFromUserName(string $fromUserName): MessageInterface;

    public function setToUserName(string $toUserName): MessageInterface;
}