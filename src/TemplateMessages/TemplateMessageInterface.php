<?php
namespace XinFox\WechatPlatform\TemplateMessages;

/**
 * Interface TemplateMessageInterface
 * @package XinFox\WechatPlatform\TemplateMessages
 */
interface TemplateMessageInterface
{
    public function toArray();
    public function getOptions();
    public function getAppId(): string;
}
