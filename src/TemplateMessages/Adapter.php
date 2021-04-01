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

    public function toJson()
    {
        $array = [
            'touser'      => $this->touser,
            'template_id' => $this->id,
            'url'         => $this->url,
            'miniprogram' => $this->miniprogram,
            'data'        => $this->getOptions(),
        ];

        return json_encode($array);
    }

    /**
     * @return bool
     */
    public function push()
    {
        $config = container('config')->path('vendor.weixin.platform');

        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($config->appid);

        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$token}";

        $pushJson = $this->toJson();

        $client = new Client();
        $response = $client->post($url, ['body' => json_encode($pushJson)]);

        $result = json_decode($response->body);
        if ($result->errcode != 0) {
            return false;
        }

        return true;
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
    }

    /**
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /*
     * @param array $miniprogram
     */
    public function setMiniprogram($miniprogram)
    {
        $this->miniprogram = $miniprogram;
    }

    /**
     * 截取字符串
     *
     * $str     字符串
     * $len     截取长度
     * $suffix  代替符号
     *
     * @return string
     */
    protected function intercept($str, $len, $suffix = "...")
    {
        if (mb_strlen($str) > $len) {
            $str = function_exists('mb_substr') ? mb_substr($str, 0, $len, "utf-8") : substr($str, 0, $len);
            $str = $str . $suffix;
        }

        return $str;
    }
}
