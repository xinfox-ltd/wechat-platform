<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */

declare(strict_types=1);

namespace XinFox\WechatPlatform;

use XinFox\WechatPlatform\Exception\InvalidArgumentException;
use XinFox\WechatPlatform\MiniProgram\Auth;

/**
 * Class MiniProgram
 * @property Auth $auth
 * @property \XinFox\WechatPlatform\MiniProgram\QrCode $qrCode
 * @property \XinFox\WechatPlatform\MiniProgram\Code $code
 * @property \XinFox\WechatPlatform\MiniProgram\CodeTemplate $codeTemplate
 * @property \XinFox\WechatPlatform\MiniProgram\Config $config
 * @property \XinFox\WechatPlatform\MiniProgram\Basics $basics
 * @package XinFox\WechatPlatform
 */
class MiniProgram extends AbstractApi
{
    /**
     * @throws \XinFox\WechatPlatform\Exception\InvalidArgumentException
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $name)
    {
        $class = 'XinFox\\WechatPlatform\\MiniProgram\\' . ucfirst($name);
        if (!class_exists($class)) {
            throw new InvalidArgumentException("$class not exists");
        }

        return new $class($this->platform);
    }

    /**
     * 快速注册企业小程序
     *
     * @param array $data
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     * @link https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/register-management/fast-registration-ent/registerMiniprogram.html
     */
    public function quickRegister(array $data): array
    {
        $token = $this->platform->getComponentAccessToken();
        $api = "/cgi-bin/component/fastregisterweapp?action=create&component_access_token={$token}";
        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 复用公众号主体快速注册小程序
     *
     * @param string $authorizerAppId
     * @param string $ticket 公众号扫码授权的凭证(公众平台扫码页面回跳到第三方平台时携带)，要看复用公众号主体快速注册小程序使用说明
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     * @link https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/product/Register_Mini_Programs/fast_registration_of_mini_program.html
     */
    public function registerMiniprogramByOffiaccount(string $authorizerAppId, string $ticket): array
    {
        $accessToken = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "/cgi-bin/account/fastregister?access_token={$accessToken}";
        return HttpClient::getInstance()
            ->post($api, ['ticket' => $ticket]);
    }

    /**
     * 查询创建任务状态
     *
     * @param array $data {"name": "企业名",  "legal_persona_wechat": "法人微信", "legal_persona_name": "法人姓名"}
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function queryRegisterStatus(array $data): array
    {
        $token = $this->platform->getComponentAccessToken();
        $api = "/cgi-bin/component/fastregisterweapp?action=search&component_access_token={$token}";
        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 快速注册个人小程序
     *
     * @param array $data {idname:"个人用户名字", wxuser: "个人用户微信号", component_phone: "第三方联系电话"}
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function fastRegisterPersonalMp(array $data): array
    {
        $token = $this->platform->getComponentAccessToken();
        $api = "/wxa/component/fastregisterpersonalweapp?action=create&component_access_token={$token}";
        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 查询创建任务状态接口详情
     *
     * @param string $taskId 任务id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function queryFastRegisterPersonalMp(string $taskId): array
    {
        $token = $this->platform->getComponentAccessToken();
        $api = "/wxa/component/fastregisterpersonalweapp?action=query&component_access_token={$token}";
        return HttpClient::getInstance()
            ->post($api, ['taskid' => $taskId]);
    }
}
