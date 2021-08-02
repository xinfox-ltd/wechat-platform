<?php

namespace XinFox\WechatPlatform;

/**
 * Class Message
 * @property string $msgId
 * @property string $content
 * @property string $fromUserName
 * @property string $toUserName
 * @property string $event
 * @property int $createTime
 *
 * @package XinFox\WechatPlatform
 */
class Message
{
    protected $data;

    /**
     * Message constructor.
     */
    public function __construct($plaintext)
    {
        $this->data = $plaintext;
    }

    /**
     * @param MessageInterface $message
     * @return MessageInterface
     */
    public function reply(MessageInterface $message): MessageInterface
    {
        $message->setFromUserName($this->toUserName)
            ->setToUserName($this->fromUserName);

        return $message;
    }

    public function getMsgType()
    {
        return $this->data['MsgType'] ?? 'none';
    }

    public function isText(): bool
    {
        return $this->getMsgType() == 'text';
    }

    public function isImage(): bool
    {
        return $this->getMsgType() == 'image';
    }

    public function isVoice(): bool
    {
        return $this->getMsgType() == 'voice';
    }

    public function isVideo(): bool
    {
        return $this->getMsgType() == 'video';
    }

    public function isShortvideo(): bool
    {
        return $this->getMsgType() == 'shortvideo';
    }

    public function isLocation(): bool
    {
        return $this->getMsgType() == 'location';
    }

    public function isLink(): bool
    {
        return $this->getMsgType() == 'link';
    }

    public function isEvent(): bool
    {
        return $this->getMsgType() == 'event';
    }

    public function isSubscribeEvent(): bool
    {
        return $this->isEvent() && $this->event == 'subscribe';
    }

    public function isUnsubscribeEvent(): bool
    {
        return $this->isEvent() && $this->event == 'unsubscribe';
    }

    public function isWeAppAuditEvent(): bool
    {
        return $this->isEvent() && in_array($this->event, ['weapp_audit_success', 'weapp_audit_fail', 'weapp_audit_delay']);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        $key = ucfirst($name);
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        throw new \InvalidArgumentException(
            sprintf("%s不存在，当前消息类型为[%s]", $key, $this->getMsgType())
        );
    }
}