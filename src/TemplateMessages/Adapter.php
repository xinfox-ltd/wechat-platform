<?php
namespace XinFox\WechatPlatform\TemplateMessages;

use GuzzleHttp\Client;
use XinFox\WechatPlatform\Api\ThirdPartyPlatform;

/**
 * Class Adapter
 * @package XinFox\WechatPlatform\TemplateMessages
 */
abstract class Adapter
{
    protected $id;

    protected $touser;

    protected $url;

    protected $miniprogram;

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
