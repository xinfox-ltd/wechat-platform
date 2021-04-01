<?php

namespace XinFox\WechatPlatform;

use DOMDocument;

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
     * @throws Exception
     */
    public function __construct()
    {
        $xmltext = file_get_contents("php://input");
        container('logger')->debug($xmltext);

        $xml = new DOMDocument();
        $xml->loadXML($xmltext);
        $encryptElement = $xml->getElementsByTagName('Encrypt');
        $toUserNameElement = $xml->getElementsByTagName('ToUserName');

        if ($encryptElement->count() == 0) {
            throw new Exception('微信平台通知内容解析失败');
        }

        $ciphertext = $encryptElement->item(0)->nodeValue;
        container('logger')->debug($ciphertext);
        if ($toUserNameElement->count() > 0) {
            $tousername = $toUserNameElement->item(0)->nodeValue;
        }

        $weixinCrypt = new Crypt();

        $plaintext = $weixinCrypt->decrypt($ciphertext);
        $this->data = XMLParse::extract($plaintext);

        container('logger')->debug(var_export($this->data, true));
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
        return $this->isEvent()
            ? $this->event == 'subscribe'
            : false;
    }

    public function isUnsubscribeEvent(): bool
    {
        return $this->isEvent()
            ? $this->event == 'unsubscribe'
            : false;
    }

    /**
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        $key = ucfirst($name);
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        throw new Exception(
            sprintf("%s不存在，当前消息类型为[%s]", $key, $this->getMsgType())
        );
    }
}