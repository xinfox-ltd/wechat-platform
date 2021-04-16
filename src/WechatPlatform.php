<?php

namespace XinFox\WechatPlatform;

use GuzzleHttp\Exception\GuzzleException;
use Psr\SimpleCache\CacheInterface;
use XinFox\WechatPlatform\Exception\ApiException;
use XinFox\WechatPlatform\Exception\AuthorizationNotExistException;
use XinFox\WechatPlatform\Exception\ComponentVerifyTicketException;
use XinFox\WechatPlatform\Exception\InvalidArgumentException;

/**
 * Class ThirdPartyPlatform
 * @property Crypt $crypt
 * @property MiniProgram $miniProgram
 * @package XinFox\WechatPlatform
 */
class WechatPlatform
{
    private Config $config;

    private CacheInterface $cache;

    private AuthorizationRepository $authorizationRepository;

    public function __construct(
        Config $config,
        CacheInterface $cache,
        AuthorizationRepository $authorizationRepository
    ) {
        $this->config = $config;
        $this->cache = $cache;
        $this->authorizationRepository = $authorizationRepository;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return CacheInterface
     */
    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * @return AuthorizationRepository
     */
    public function getAuthorizationRepository(): AuthorizationRepository
    {
        return $this->authorizationRepository;
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function receiveAuthEventPush(string $xmlText = null): array
    {
        if (!$xmlText) {
            $xmlText = file_get_contents("php://input");
        }
        $xml = XMLParse::extract($xmlText);
        $data = $this->crypt->decrypt($xml['Encrypt']);

        if ($data['InfoType'] == 'component_verify_ticket') {
            $this->setComponentVerifyTicket($data['ComponentVerifyTicket']);
        }

        return $data;
    }

    /**
     * 生成授权url
     *
     * @param string $preAuthCode 预授权码
     * @param string $redirectUri 回调 URI
     * @param string $mode 授权方式：wap 点击移动端链接快速授权, scan 授权注册页面扫码授权
     * @param string $bizAppId 指定授权唯一的小程序或公众号
     * @return string
     */
    public function generateAuthUrl(
        string $preAuthCode,
        string $redirectUri,
        $mode = 'wap',
        string $bizAppId = ''
    ): string {
        $redirectUri = urlencode($redirectUri);
        if ($mode == 'wap') {
            return $this->generateLinkAuthUrl($preAuthCode, $redirectUri, $bizAppId);
        } else {
            return $this->generateScanAuthUrl($preAuthCode, $redirectUri, $bizAppId);
        }
    }

    /**
     * 生成移动端快速授权链接
     *
     * @param string $preAuthCode 预授权码
     * @param string $redirectUri 回调 URI
     * @param string $bizAppId 指定授权唯一的小程序或公众号
     * @return string
     */
    public function generateLinkAuthUrl(string $preAuthCode, string $redirectUri, string $bizAppId = ''): string
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=%s'
            . '&pre_auth_code=%s&redirect_uri=%s&auth_type=3&biz_appid=%s';
        return sprintf(
            $url,
            $this->config->getAppId(),
            $preAuthCode,
            $redirectUri,
            $bizAppId
        );
    }

    /**
     * 生成授权扫码授权注册页面链接
     *
     * @param string $preAuthCode 预授权码
     * @param string $redirectUri 回调 URI
     * @param string $bizAppId 指定授权唯一的小程序或公众号
     * @return string
     */
    public function generateScanAuthUrl(string $preAuthCode, string $redirectUri, string $bizAppId = ''): string
    {
        $url = 'https://mp.weixin.qq.com/safe/bindcomponent?action=bindcomponent&no_scan=1&component_appid=%s'
            . '&pre_auth_code=%s&redirect_uri=%s&auth_type=3&biz_appid=%s#wechat_redirect';
        return sprintf(
            $url,
            $this->config->getAppId(),
            $preAuthCode,
            $redirectUri,
            $bizAppId
        );
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws GuzzleException
     * @throws Exception\ApiException|ComponentVerifyTicketException
     */
    public function getComponentAccessToken(string $componentVerifyTicket = null)
    {
        if ($this->cache->has('component_access_token')) {
            return $this->cache->get('component_access_token');
        } else {
            $componentVerifyTicket = $componentVerifyTicket ?? $this->getComponentVerifyTicket();

            $data = [
                'component_appid' => $this->config->getAppId(),
                'component_appsecret' => $this->config->getAppSecret(),
                'component_verify_ticket' => $componentVerifyTicket,
            ];

            $response = HttpClient::getInstance()->post('/cgi-bin/component/api_component_token', $data);

            $this->cache->set(
                'component_access_token',
                $response['component_access_token'],
                $response['expires_in'] - 120
            );

            return $response['component_access_token'];
        }
    }

    /**
     * 获取预授权码pre_auth_code
     *
     * @param string|null $componentAccessToken
     * @return mixed
     * @throws Exception\ApiException
     * @throws GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException|ComponentVerifyTicketException
     */
    public function getPreAuthCode(string $componentAccessToken = null)
    {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();
        $data = [
            'component_appid' => $this->config->getAppId(),
        ];

        $uri = "/cgi-bin/component/api_create_preauthcode?component_access_token=" . $componentAccessToken;
        $response = HttpClient::getInstance()
            ->post($uri, $data);

        return $response['pre_auth_code'];
    }

    /**
     * 通过授权码和自己的接口调用凭据（component_access_token），
     * 换取公众号或小程序的接口调用凭据（authorizer_access_token 和用于前者快过期时用来刷新它的 authorizer_refresh_token）和授权信息（授权了哪些权限等信息）
     *
     * @param string $authorizationCode 授权code,会在授权成功时返回给第三方平台，详见第三方平台授权流程说明，不是预授权码
     * @param string|null $componentAccessToken 第三方平台component_access_token，不是authorizer_access_token，通过接口 getComponentAccessToken获取
     * @return Authorization
     * @throws Exception\ApiException
     * @throws GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException|ComponentVerifyTicketException
     */
    public function queryAuth(string $authorizationCode, string $componentAccessToken = null): Authorization
    {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();
        $data = [
            'component_appid' => $this->config->getAppId(),
            'authorization_code' => $authorizationCode,
        ];

        $uri = "/cgi-bin/component/api_query_auth?component_access_token={$componentAccessToken}";
        $response = HttpClient::getInstance()
            ->post($uri, $data);

        $authorization = new Authorization($response);
        $this->authorizationRepository->save($authorization);

        return $authorization;
    }

    /**
     * 获取授权方access_token
     *
     * @param string $authorizerAppId
     * @return mixed
     * @throws AuthorizationNotExistException
     * @throws ComponentVerifyTicketException
     * @throws Exception\ApiException
     * @throws GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getAuthorizerAccessToken(string $authorizerAppId): Authorization
    {
        $authorization = $this->authorizationRepository->getOneByAuthorizerAppId($authorizerAppId);
        if (!$authorization) {
            throw new AuthorizationNotExistException();
        }

        $time = time();
        if ($authorization->getExpireTime() < $time || $authorization->getExpireTime() - $time < 60) {
            // 刷新token
            // TODO 如果高并发需要另外处理
            $authorization = $this->refreshAuthorizerToken($authorizerAppId, $authorization->getRefreshToken());
        }

        return $authorization;
    }

    /**
     * 获取（刷新）授权公众号或小程序的接口调用凭据（令牌）
     *
     * @param string $authorizerAppId
     * @param string $authorizerRefreshToken
     * @param string|null $componentAccessToken
     * @return Authorization
     * @throws ComponentVerifyTicketException
     * @throws Exception\ApiException
     * @throws GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function refreshAuthorizerToken(
        string $authorizerAppId,
        string $authorizerRefreshToken,
        string $componentAccessToken = null
    ): Authorization {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();

        $data = [
            'component_appid' => $this->config->getAppId(),
            'authorizer_appid' => $authorizerAppId,
            'authorizer_refresh_token' => $authorizerRefreshToken,
        ];

        $uri = "/cgi-bin/component/api_authorizer_token?component_access_token={$componentAccessToken}";
        $authorization = new Authorization(
            HttpClient::getInstance()
                ->post($uri, $data)
        );
        return $this->authorizationRepository->update(
            $authorization['authorizer_access_token'],
            (int)$authorization['expires_in'],
            $authorization['authorizer_refresh_token']
        );
    }

    /**
     * 获取授权方的帐号基本信息
     *
     * @param string $authorizerAppId
     * @param string|null $componentAccessToken
     * @return Authorizer
     * @throws ApiException
     * @throws ComponentVerifyTicketException
     * @throws GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getAuthorizerInfo(string $authorizerAppId, string $componentAccessToken = null): Authorizer
    {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();

        $data = [
            'component_appid' => $this->config->getAppId(),
            'authorizer_appid' => $authorizerAppId,
        ];

        $uri = "/cgi-bin/component/api_get_authorizer_info?component_access_token=" . $componentAccessToken;
        $response = HttpClient::getInstance()
            ->post($uri, $data);

        return new Authorizer($response);
    }

    /**
     * 获取授权方的选项设置信息
     *
     * @param string $authorizerAppId
     * @param string $optionName
     * @param string|null $componentAccessToken
     * @return array
     * @throws ComponentVerifyTicketException
     * @throws GuzzleException
     * @throws ApiException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function getAuthorizerOption(
        string $authorizerAppId,
        string $optionName,
        string $componentAccessToken = null
    ): array {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();

        $data = [
            'component_appid' => $this->config->getAppId(),
            'authorizer_appid' => $authorizerAppId,
            'option_name' => $optionName
        ];

        $uri = "/cgi-bin/component/api_get_authorizer_option?component_access_token=" . $componentAccessToken;
        return HttpClient::getInstance()
            ->post($uri, $data);
    }

    /**
     * 获取授权方的选项设置信息
     *
     * location_report（地理位置上报选项）{0无上报 1进入会话时上报 2每5s上报
     * voice_recognize（语音识别开关选项）{0关闭语音识别 1开启语音识别
     * customer_service（多客服开关选项）{0关闭多客服 1开启多客服
     *
     * @param string $authorizerAppId
     * @param string $optionName
     * @param string $optionValue
     * @param string|null $componentAccessToken
     * @return array
     * @throws ApiException
     * @throws ComponentVerifyTicketException
     * @throws GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setAuthorizerOption(
        string $authorizerAppId,
        string $optionName,
        string $optionValue,
        string $componentAccessToken = null
    ): array {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();

        $data = [
            'component_appid' => $this->config->getAppId(),
            'authorizer_appid' => $authorizerAppId,
            'option_name' => $optionName,
            'option_value' => $optionValue
        ];

        $uri = "/cgi-bin/component/api_set_authorizer_option?component_access_token=" . $componentAccessToken;
        return HttpClient::getInstance()
            ->post($uri, $data);
    }

    /**
     * 拉取所有已授权的帐号信息
     * @param int $offset
     * @param int $count
     * @return array
     * @throws ApiException
     * @throws ComponentVerifyTicketException
     * @throws GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getAuthorizerList(int $offset, int $count): array
    {
        $componentAccessToken = $componentAccessToken ?? $this->getComponentAccessToken();

        $data = [
            'component_appid' => $this->config->getAppId(),
            'offset' => $offset,
            'count' => $count,
        ];

        $uri = "/cgi-bin/component/api_set_authorizer_option?component_access_token=" . $componentAccessToken;
        return HttpClient::getInstance()
            ->post($uri, $data);
    }

    /**
     * @param string $abstract
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get(string $abstract)
    {
        if ($abstract == 'crypt') {
            return new Crypt($this->config->getAppId(), $this->config->getToken(), $this->config->getEncryptKey());
        } else {
            $class = 'XinFox\\WechatPlatform\\' . ucfirst($abstract);
            if (class_exists($class)) {
                return new $class($this);
            }
        }
        throw new InvalidArgumentException();
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @return string
     * @throws ComponentVerifyTicketException
     */
    public function getComponentVerifyTicket(): string
    {
        try {
            $componentVerifyTicket = $this->cache->get('component_verify_ticket');
            if (!$componentVerifyTicket) {
                throw new ComponentVerifyTicketException();
            }

            return $componentVerifyTicket;
        } catch (\Psr\SimpleCache\InvalidArgumentException $exception) {
            throw new ComponentVerifyTicketException();
        }
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setComponentVerifyTicket(string $componentVerifyTicket)
    {
        $this->cache->set('component_verify_ticket', $componentVerifyTicket, 3600 * 11);
    }
}