<?php

namespace XinFox\WechatPlatform\MiniProgram;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use XinFox\WechatPlatform\AbstractApi;
use XinFox\WechatPlatform\HttpClient;

/**
 * 代码管理
 * @package XinFox\WechatPlatform\MiniProgram
 */
class Code extends AbstractApi
{
    /**
     * 为授权的小程序帐号上传小程序代码
     *
     * @param string $authorizerAppId
     * @param array $data
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function commit(string $authorizerAppId, array $data): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/commit?access_token={$token}";

        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 获取体验小程序的体验二维码
     *
     * @param string $authorizerAppId
     * @param string|null $path
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getExperienceQrcode(string $authorizerAppId, string $path = null): ResponseInterface
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $url = "https://api.weixin.qq.com/wxa/get_qrcode?access_token={$token}";
        if ($path !== null) {
            $url .= "&path=" . urlencode($path);
        }

        $client = new Client();
        return $client->get($url);
    }

    /**
     * 获取授权小程序帐号已设置的类目
     *
     * @param string $authorizerAppId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getCategory(string $authorizerAppId): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/get_category?access_token={$token}";

        return HttpClient::getInstance()
            ->get($api);
    }

    /**
     * 获取小程序的第三方提交代码的页面配置
     *
     * @param string $authorizerAppId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getPage(string $authorizerAppId): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/get_page?access_token={$token}";

        return HttpClient::getInstance()
            ->get($api);
    }

    /**
     * 将第三方提交的代码包提交审核
     *
     * @param string $authorizerAppId
     * @param array $data
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function submitAudit(string $authorizerAppId, array $data): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/submit_audit?access_token={$token}";

        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 查询某个指定版本的审核状态
     *
     * @param string $authorizerAppId
     * @param $auditId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getAuditStatus(string $authorizerAppId, $auditId): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/get_auditstatus?access_token={$token}";
        $data = [
            'auditid' => $auditId
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 查询最新一次提交的审核状态
     *
     * @param string $authorizerAppId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getLatestAuditStatus(string $authorizerAppId): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/get_latest_auditstatus?access_token={$token}";

        return HttpClient::getInstance()
            ->get($api);
    }

    /**
     * 小程序审核撤回
     *
     * @param string $authorizerAppId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function undoCodeAudit(string $authorizerAppId): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/undocodeaudit?access_token={$token}";

        return HttpClient::getInstance()
            ->get($api);
    }

    /**
     * 发布已通过审核的小程序
     *
     * @param string $authorizerAppId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function release(string $authorizerAppId): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/release?access_token={$token}";

        return HttpClient::getInstance()
            ->post($api, "{}");
    }

    /**
     * 分阶段发布
     *
     * @param string $authorizerAppId
     * @param int $grayPercentage 灰度的百分比，1到100的整数
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function grayRelease(string $authorizerAppId, int $grayPercentage): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/grayrelease?access_token={$token}";
        $data = [
            'gray_percentage' => $grayPercentage,
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 查询当前分阶段发布详情
     *
     * @param string $authorizerAppId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getGrayreleaseplan(string $authorizerAppId): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/getgrayreleaseplan?access_token={$token}";

        return HttpClient::getInstance()
            ->get($api);
    }

    /**
     * 取消分阶段发布
     *
     * @param string $authorizerAppId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function revertGrayRelease(string $authorizerAppId): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/revertgrayrelease?access_token={$token}";

        return HttpClient::getInstance()
            ->get($api);
    }

    /**
     * 修改小程序线上代码的可见状态
     *
     * @param string $authorizerAppId
     * @param string $action 设置可访问状态，发布后默认可访问，close为不可见，open为可见
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function changeVisitStatus(string $authorizerAppId, string $action): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/change_visitstatus?access_token={$token}";
        $data = [
            'action' => $action == 'close' ? 'close' : 'open'
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 小程序版本回退
     *
     * @param string $authorizerAppId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function revertCodeRelease(string $authorizerAppId): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/revertcoderelease?access_token={$token}";

        return HttpClient::getInstance()
            ->get($api);
    }

    /**
     * 获取可回退的小程序版本
     * 调用本接口可以获取可回退的小程序版本（最多保存最近发布或回退的5个版本）
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     */
    public function getHistoryVersion(string $authorizerAppId): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/revertcoderelease?action=get_history_version&access_token={$token}";

        return HttpClient::getInstance()
            ->get($api);
    }

    /**
     * 查询当前设置的最低基础库版本及各版本用户占比
     *
     * @param string $authorizerAppId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getWeAppSupportVersion(string $authorizerAppId): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/getweappsupportversion?access_token={$token}";
        $data = [];

        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 设置最低基础库版本
     *
     * @param string $authorizerAppId
     * @param mixed $version
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\AuthorizationNotExistException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function setWeAppSupportVersion(string $authorizerAppId, $version): array
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = "https://api.weixin.qq.com/wxa/setweappsupportversion?access_token={$token}";
        $data = [
            'version' => $version
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }
}