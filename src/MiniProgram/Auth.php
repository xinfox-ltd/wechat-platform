<?php

namespace XinFox\WechatPlatform\MiniProgram;

use GuzzleHttp\Exception\GuzzleException;
use Psr\SimpleCache\InvalidArgumentException;
use XinFox\WechatPlatform\AbstractApi;
use XinFox\WechatPlatform\Exception;
use XinFox\WechatPlatform\HttpClient;

class Auth extends AbstractApi
{
    /**
     * @param string $appId
     * @param string $jsCode
     * @return array
     * @throws Exception\ApiException
     * @throws Exception\ComponentVerifyTicketException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function code2Session(string $appId, string $jsCode): array
    {
        $componentAccessToken = $this->platform->getComponentAccessToken();

        $data = HttpClient::getInstance()
            ->get(
                '/sns/component/jscode2session',
                [
                    'appid' => $appId,
                    'js_code' => $jsCode,
                    'grant_type' => 'authorization_code',
                    'component_appid' => $this->platform->getConfig()->getAppId(),
                    'component_access_token' => $componentAccessToken
                ]
            );

        $key = $this->createSessionKeyCacheKey($appId, $data['openid']);
        $this->platform->getCache()->set($key, $data['session_key'], 2592000);

        return $data;
    }

    /**
     * @param string $appId
     * @param string $openId
     * @return string
     * @throws InvalidArgumentException|Exception\SessionKeyException
     */
    public function getSessionKey(string $appId, string $openId): string
    {
        $key = $this->createSessionKeyCacheKey($appId, $openId);
        $cache = $this->platform->getCache();
        if ($cache->has($key)) {
            return $cache->get($key);
        }

        throw new Exception\SessionKeyException('小程序登录 SessionKey 失效');
    }

    /**
     * 开放数据解密
     * @param string $appId
     * @param string $openId
     * @param string $encryptedData
     * @param string $iv
     * @return array
     * @throws Exception\DecryptException
     * @throws Exception\InvalidArgumentException
     * @throws Exception\SessionKeyException
     * @throws InvalidArgumentException
     */
    public function decryptData(string $appId, string $openId, string $encryptedData, string $iv): array
    {
        if (strlen($iv) != 24) {
            throw new Exception\InvalidArgumentException('IV 长度不能小于24位');
        }

        $sessionKey = $this->getSessionKey($appId, $openId);

        $aesKey = base64_decode($sessionKey);
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $json = json_decode($result, true);
        if ($json == null) {
            throw new Exception\DecryptException('数据解密失败');
        }

        return $json;
    }

    /**
     * @param string $appId
     * @param string $openId
     * @return string
     */
    private function createSessionKeyCacheKey(string $appId, string $openId): string
    {
        return md5($appId . $openId . 'session_key');
    }
}