<?php

namespace XinFox\WechatPlatform\Miniprogram;

use XinFox\WechatPlatform\Api\ThirdPartyPlatform;
use XinFox\WechatPlatform\Exception;
use XinFox\WechatPlatform\Http;

class Config
{

    /**
     * 设置小程序服务器域名
     *
     * @param string $authorizerAppid
     * @param string $action
     * @param array $domain
     * @return array
     * @throws Exception
     */
    public function modifyDomain(string $authorizerAppid, string $action, array $domain = [])
    {
        $accessToken = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);
        $api = 'https://api.weixin.qq.com/wxa/modify_domain?access_token=' . $accessToken;
        $data = [
            'action' => $action,
            'requestdomain' => '',
            'wsrequestdomain' => '',
            'uploaddomain' => '',
            'downloaddomain' => '',
        ];

        $data = array_merge($data, $domain);

        $data = Http::getInstance()
            ->post($api, $data);

        return $data;
    }

    /**
     * @param string $authorizerAppId
     * @param string $action
     * @param string $requestDomain
     * @return array
     * @throws Exception
     */
    public function setWebViewDomain(string $authorizerAppId, string $action, string $requestDomain): array
    {
        $accessToken = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppId);
        $api = 'https://api.weixin.qq.com/wxa/setwebviewdomain?access_token=' . $accessToken;
        $data = [
            'action' => $action,
            'requestdomain' => $requestDomain,
        ];

        $data = Http::getInstance()
            ->post($api, $data);

        return $data;
    }
}