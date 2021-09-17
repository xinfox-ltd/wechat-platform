<?php

namespace XinFox\WechatPlatform\TemplateMessages;

/**
 * Class AbstractTemplateMessage
 * @package XinFox\WechatPlatform\TemplateMessages
 */
abstract class AbstractTemplateMessage implements TemplateMessageInterface
{
    protected $appid;

    protected $id;

    protected $touser;

    protected $url;

    protected $miniprogram;

    public function __construct($appid)
    {
        $this->appid = $appid;
    }

    public function toArray()
    {
        return [
            'touser'      => $this->touser,
            'template_id' => $this->id,
            'url'         => $this->url,
            'miniprogram' => $this->miniprogram,
            'data'        => $this->getOptions(),
        ];
    }

    public function getAppId(): string
    {
        return $this->appid;
    }

    /**
     *
     * @return string $touser
     */
    public function getTouser()
    {
        return $this->touser;
    }

    /**
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     *
     * @param string $touser
     */
    public function setTouser($touser)
    {
        $this->touser = $touser;
        return $this;
    }

    /**
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /*
     * @param array $miniprogram
     */
    public function setMiniprogram($miniprogram)
    {
        $this->miniprogram = $miniprogram;
        return $this;
    }
}
