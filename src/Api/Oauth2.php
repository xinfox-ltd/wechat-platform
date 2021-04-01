<?php

namespace XinFox\WechatPlatform\Api\ThirdPartyPlatform;

use XinFox\WechatPlatform\Api\ThirdPartyPlatform;
use XinFox\WechatPlatform\Exception;
use XinFox\WechatPlatform\Http;

class Oauth2
{
    protected string $componentAppId;

    public function __construct(string $componentAppId)
    {
        $this->componentAppId = $componentAppId;
    }

    /**d
     * @param string $appId
     * @param string $redirectUri
     * @return string
     */
    public function generateAuthUrl(string $appId, string $redirectUri): string
    {
        $componentAppId = $this->componentAppId;
        //$redirectUri = $redirectUri ?? urlencode(container('url')->get('/weixin/oauth2/authorize/callback'));
        $scope = 'snsapi_userinfo';

        $cacheKeyName = "{$appId}_state";
        $safety = new Safety($cacheKeyName);
        $safety->create(600);

        $state = $safety->getState();

        return "https://open.weixin.qq.com/connect/oauth2/authorize?"
            . "appid={$appId}&redirect_uri={$redirectUri}&response_type=code&scope={$scope}&state={$state}&component_appid={$componentAppId}#wechat_redirect";
    }

    /**
     * @param string $appId
     * @param string $code
     * @param string $state
     * @return array|null
     * @throws Exception
     */
    public function getAccessTokenByCode(string $appId, string $code, string $state): ?array
    {
        $cache = container('cache');
        $cacheKeyName = "{$appId}_state";
        if (!$cache->has($cacheKeyName)) {
            throw new Exception('非法操作:state');
        }

        if ($cache->get($cacheKeyName) != $state) {
            throw new Exception('非法操作:state');
        }

        $componentAppId = $this->componentAppId;
        $componentAccessToken = ThirdPartyPlatform::getInstance()->getComponentAccessToken();

        $api = "https://api.weixin.qq.com/sns/oauth2/component/access_token?"
            . "appid={$appId}&code={$code}&grant_type=authorization_code&component_appid={$componentAppId}&component_access_token={$componentAccessToken}";

        $data = Http::getInstance()->get($api);

        $cache->delete($cacheKeyName);

        return $data;
    }

    public function getAccessToken(string $appId): ?string
    {
        $cache = container('cache');
        $cacheKeyName = "{$appId}_state";
        if (!$cache->has($cacheKeyName)) {
            return null;
        }

        return $cache->get($cacheKeyName);
    }

    /**
     * @param string $appId
     * @param string $refreshToken
     * @return array
     * @throws Exception
     */
    public function refreshAccessToken(string $appId, string $refreshToken): array
    {
        $componentAppId = $this->componentAppId;
        $componentAccessToken = ThirdPartyPlatform::getInstance()->getComponentAccessToken();

        $api = "https://api.weixin.qq.com/sns/oauth2/component/refresh_token?"
            . "appid={$appId}&grant_type=refresh_token&component_appid={$componentAppId}&component_access_token={$componentAccessToken}&refresh_token={$refreshToken}";

        return Http::getInstance()->get($api);
    }

    /**
     * @param string $accessToken
     * @param string $openid
     * @return array
     * @throws Exception
     */
    public function getUserinfo(string $accessToken, string $openid): array
    {
        $api = "https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openid}&lang=zh_CN";

        return Http::getInstance()->get($api);
    }
}