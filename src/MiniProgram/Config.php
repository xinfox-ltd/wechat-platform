<?php

namespace XinFox\WechatPlatform\MiniProgram;

use XinFox\WechatPlatform\AbstractApi;
use XinFox\WechatPlatform\HttpClient;

class Config extends AbstractApi
{
    /**
     * 设置小程序服务器域名
     *
     * @param string $authorizerAppId
     * @param string $action
     * @param array $domain
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function modifyDomain(string $authorizerAppId, string $action, array $domain = []): array
    {
        $accessToken = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = 'https://api.weixin.qq.com/wxa/modify_domain?access_token=' . $accessToken;
        $data = [
            'action' => $action,
            'requestdomain' => '',
            'wsrequestdomain' => '',
            'uploaddomain' => '',
            'downloaddomain' => '',
        ];

        $data = array_merge($data, $domain);

        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * @param string $authorizerAppId
     * @param string $action
     * @param string $requestDomain
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function setWebViewDomain(string $authorizerAppId, string $action, string $requestDomain): array
    {
        $accessToken = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = 'https://api.weixin.qq.com/wxa/setwebviewdomain?access_token=' . $accessToken;
        $data = [
            'action' => $action,
            'requestdomain' => $requestDomain,
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }
}