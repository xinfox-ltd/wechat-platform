<?php

namespace XinFox\WechatPlatform\Custom\Message;

class Text implements MessageInterface
{
    protected $data = [];

    protected $authorizerAccessToken;

    /**
     * Text constructor.
     * @param string $touser OPENID
     * @param string $content
     */
    public function __construct(string $touser, string $content)
    {
        $this->data['touser'] = $touser;
        $this->data['msgtype'] = 'text';
        $this->data['text']['content'] = $content;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}