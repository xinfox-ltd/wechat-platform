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
 * @property OpenPlatform $openPlatform
 * @property TemplateMessage $templateMessage
 * @property SubscribeMessage $subscribeMessage
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

    public function receivePush($xmlText): Message
    {
        $xml = XMLParse::extract($xmlText);
        return new Message($this->crypt->decrypt($xml['Encrypt']));
    }

    /**
     * @param string|null $xmlText
     * @return array
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
            //$this->renewComponentAccessToken($data['ComponentVerifyTicket']);
        }

        return $data;
    }

    /**
     * 生成授权url
     *
     * @param string $redirectUri 回调 URI
     * @param string $mode 授权方式：wap 点击移动端链接快速授权, scan 授权注册页面扫码授权
     * @param int $authType
     * @param string $bizAppId 指定授权唯一的小程序或公众号
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function generateAuthUrl(
        string $redirectUri,
        string $mode = 'wap',
        int $authType = 3,
        string $bizAppId = ''
    ): string {
        $redirectUri = urlencode($redirectUri);
        if ($mode == 'wap') {
            return $this->generateH5AuthUrl($redirectUri, $authType, $bizAppId);
        }

        return $this->generateLinkAuthUrl($redirectUri, $authType, $bizAppId);
    }

    /**
     * PC版授权链接
     *
     * @param string $redirectUri
     * @param int $authType 1 表示手机端仅展示公众号；2 表示仅展示小程序，3 表示公众号和小程序都展示。如果为未指定，则默认小程序和公众号都展示。
     * @param string $bizAppId
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function generateLinkAuthUrl(string $redirectUri, int $authType = 3, string $bizAppId = ''): string
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=%s'
            . '&pre_auth_code=%s&redirect_uri=%s&auth_type=%d&biz_appid=%s';
        return sprintf(
            $url,
            $this->config->getAppId(),
            $this->getPreAuthCode(),
            $redirectUri,
            $authType,
            $bizAppId
        );
    }

    /**
     * H5版授权注册页面链接
     *
     * @param string $redirectUri 回调 URI
     * @param int $authType 1 表示手机端仅展示公众号；2 表示仅展示小程序，3 表示公众号和小程序都展示。如果为未指定，则默认小程序和公众号都展示。
     * @param string $bizAppId 指定授权唯一的小程序或公众号
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function generateH5AuthUrl(string $redirectUri, int $authType = 3, string $bizAppId = ''): string
    {
        $url = 'https://open.weixin.qq.com/wxaopen/safe/bindcomponent?action=bindcomponent&no_scan=1&component_appid=%s'
            . '&pre_auth_code=%s&redirect_uri=%s&auth_type=%d&biz_appid=%s#wechat_redirect';
        return sprintf(
            $url,
            $this->config->getAppId(),
            $this->getPreAuthCode(),
            $redirectUri,
            $authType,
            $bizAppId
        );
    }

    /**
     * @param string|null $componentVerifyTicket
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getComponentAccessToken(string $componentVerifyTicket = null)
    {
        if ($this->cache->has('component_access_token')) {
            return $this->cache->get('component_access_token');
        }

        $componentVerifyTicket = $componentVerifyTicket ?? $this->getComponentVerifyTicket();
        return $this->renewComponentAccessToken($componentVerifyTicket);
    }

    /**
     * @param string $componentVerifyTicket
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     */
    public function renewComponentAccessToken(string $componentVerifyTicket): string
    {
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
        $authorizationInfo = HttpClient::getInstance()
            ->post($uri, $data)['authorization_info'];

        $authorizationInfo['expire_time'] = time() + $authorizationInfo['expires_in'];
        $authorization = new Authorization($authorizationInfo);
        $this->authorizationRepository->save($authorization);

        return $authorization;
    }

    /**
     * 获取授权方access_token
     *
     * @param string $authorizerAppId
     * @return mixed
     * @throws ComponentVerifyTicketException
     * @throws Exception\ApiException
     * @throws GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getAuthorizerAccessToken(string $authorizerAppId): Authorization
    {
        $authorization = $this->authorizationRepository->findByAppId($authorizerAppId);

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
        $response = HttpClient::getInstance()
            ->post($uri, $data);

        $response['expire_time'] = time() + $response['expires_in'];
        $response['authorizer_appid'] = $authorizerAppId;

        return $this->authorizationRepository->save(
            new Authorization($response)
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

        $uri = "/cgi-bin/component/api_get_authorizer_info?component_access_token=$componentAccessToken";
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

    /**
     * 启动ticket推送服务
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     */
    public function apiStartPushTicket()
    {
        $data = [
            'component_appid' => $this->config->getAppId(),
            'component_secret' => $this->config->getAppSecret(),
        ];

        $uri = "/cgi-bin/component/api_start_push_ticket";
        return HttpClient::getInstance()
            ->post($uri, $data);
    }
}
