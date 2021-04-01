<?php

namespace XinFox\WechatPlatform\Message;

use XinFox\WechatPlatform\Crypt;
use XinFox\WechatPlatform\MessageInterface;

abstract class Template implements MessageInterface
{
    protected $data = [];

    public function __construct()
    {
        $this->setValue('CreateTime', time());
    }

    public function setToUserName(string $value): MessageInterface
    {
        $this->setValue('ToUserName', $value);

        return $this;
    }

    public function setFromUserName(string $value): MessageInterface
    {
        $this->setValue('FromUserName', $value);

        return $this;
    }

    protected function setValue(string $key, string $value): void
    {
        $this->data[$key] = $value;
    }

    public function toXml(): string
    {
        $xml = '<xml>';
        foreach ($this->data as $item => $value) {
            $xml .= sprintf("<%s><![CDATA[%s]]></%s>", $item, $value, $item);
        }

        $xml .= '</xml>';

        return $xml;
    }

    public function encrypt(): string
    {
        return (new Crypt())->encrypt($this->toXml());
    }

    public function __toString(): string
    {
        return $this->encrypt();
    }
}