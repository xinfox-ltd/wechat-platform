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
     * ????????????url
     *
     * @param string $redirectUri ?????? URI
     * @param string $mode ???????????????wap ?????????????????????????????????, scan ??????????????????????????????
     * @param string $bizAppId ??????????????????????????????????????????
     * @return string
     * @throws ApiException
     * @throws ComponentVerifyTicketException
     * @throws GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function generateAuthUrl(
        string $redirectUri,
        string $mode = 'wap',
        string $bizAppId = ''
    ): string {
        $redirectUri = urlencode($redirectUri);
        if ($mode == 'wap') {
            return $this->generateLinkAuthUrl($redirectUri, $bizAppId);
        } else {
            return $this->generateScanAuthUrl($redirectUri, $bizAppId);
        }
    }

    /**
     * ?????????????????????????????????
     * @param string $redirectUri
     * @param string $bizAppId
     * @return string
     * @throws ApiException
     * @throws ComponentVerifyTicketException
     * @throws GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function generateLinkAuthUrl(string $redirectUri, string $bizAppId = ''): string
    {
        $url = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=%s'
            . '&pre_auth_code=%s&redirect_uri=%s&auth_type=3&biz_appid=%s';
        return sprintf(
            $url,
            $this->config->getAppId(),
            $this->getPreAuthCode(),
            $redirectUri,
            $bizAppId
        );
    }

    /**
     * ??????????????????????????????????????????
     *
     * @param string $preAuthCode ????????????
     * @param string $redirectUri ?????? URI
     * @param string $bizAppId ??????????????????????????????????????????
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
     * ??????????????????pre_auth_code
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
     * ????????????????????????????????????????????????component_access_token??????
     * ???????????????????????????????????????????????????authorizer_access_token ????????????????????????????????????????????? authorizer_refresh_token??????????????????????????????????????????????????????
     *
     * @param string $authorizationCode ??????code,????????????????????????????????????????????????????????????????????????????????????????????????????????????
     * @param string|null $componentAccessToken ???????????????component_access_token?????????authorizer_access_token??????????????? getComponentAccessToken??????
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
     * ???????????????access_token
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
        $authorization = $this->authorizationRepository->findByAppId($authorizerAppId);
        if (!$authorization) {
            throw new AuthorizationNotExistException();
        }

        $time = time();
        if ($authorization->getExpireTime() < $time || $authorization->getExpireTime() - $time < 60) {
            // ??????token
            // TODO ?????????????????????????????????
            $authorization = $this->refreshAuthorizerToken($authorizerAppId, $authorization->getRefreshToken());
        }

        return $authorization;
    }

    /**
     * ??????????????????????????????????????????????????????????????????????????????
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
     * ????????????????????????????????????
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
     * ????????????????????????????????????
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
     * ????????????????????????????????????
     *
     * location_report??????????????????????????????{0????????? 1????????????????????? 2???5s??????
     * voice_recognize??????????????????????????????{0?????????????????? 1??????????????????
     * customer_service???????????????????????????{0??????????????? 1???????????????
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
     * ????????????????????????????????????
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
     * ??????ticket????????????
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
