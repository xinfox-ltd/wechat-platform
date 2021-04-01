<?php

namespace XinFox\WechatPlatform\Message;

class Text extends Template
{
    public function __construct(?string $content = null)
    {
        parent::__construct();

        if ($content) {
            $this->setContent($content);
        }

        $this->setValue('MsgType', 'text');
    }

    public function setContent(string $content): void
    {
        $this->setValue('Content', $content);
    }
}