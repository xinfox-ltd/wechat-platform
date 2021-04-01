<?php

namespace XinFox\WechatPlatform\Miniprogram;

use XinFox\WechatPlatform\Api\ThirdPartyPlatform;
use XinFox\WechatPlatform\Exception;
use XinFox\WechatPlatform\Http;

class Auth
{
    protected $componentAppid;

    protected static $instance;

    public function __construct()
    {
        $config = container('config')->path('vendor.weixin.platform');

        $this->componentAppid = $config->appid;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $appId
     * @param string $jsCode
     * @return array
     * @throws Exception
     */
    public function code2Session(string $appId, string $jsCode): array
    {
        $componentAccessToken = ThirdPartyPlatform::getInstance()->getComponentAccessToken();
        $format = "https://api.weixin.qq.com/sns/component/jscode2session?appid=%s&js_code=%s&grant_type=authorization_code&component_appid=%s&component_access_token=%s";

        $api = sprintf($format, $appId, $jsCode, $this->componentAppid, $componentAccessToken);
        $data = Http::getInstance()
            ->get($api);

        $key = md5($appId.$data['openid'].'SessionKey');
        container('cache')->set($key, $data['session_key'], 2592000);

        return $data;
    }

    /**
     * @param string $appid
     * @param string $openid
     * @return string
     * @throws Exception
     */
    public function getSessionKey(string $appid, string $openid): string
    {
        $key = md5($appid.$openid.'SessionKey');
        $cache = container('cache');
        if ($cache->has($key)) {
            return $cache->get($key);
        }

        throw new Exception('SessionKey失效');
    }

    /**
     * 开放数据解密
     * @param string $appid
     * @param string $openid
     * @param string $encryptedData
     * @param string $iv
     * @return array
     * @throws Exception
     */
    public function decryptData(string $appid, string $openid, string $encryptedData, string $iv): array
    {
        if (strlen($iv) != 24) {
            throw new Exception('-41002');
        }

        $sessionKey = $this->getSessionKey($appid, $openid);

        $aesKey = base64_decode($sessionKey);
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $json = json_decode($result, true);
        if ($json == null) {
            throw new Exception('-41003');
        }

        if ($json['watermark']['appid'] != $appid) {
            throw new Exception('-41003');
        }

        return $json;
    }
}