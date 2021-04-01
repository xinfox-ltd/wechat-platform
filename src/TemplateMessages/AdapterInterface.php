<?php
namespace XinFox\WechatPlatform\TemplateMessages;

/**
 * Interface AdapterInterface
 * @package XinFox\WechatPlatform\TemplateMessages
 */
interface AdapterInterface
{
    public function toJson();
    public function getOptions();
}
