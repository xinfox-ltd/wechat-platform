<?php

namespace XinFox\WechatPlatform\MiniProgram;

use XinFox\WechatPlatform\AbstractApi;
use XinFox\WechatPlatform\HttpClient;

class CodeTemplate extends AbstractApi
{
    /**
     * 获取草稿箱内的所有临时代码草稿
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getTemplateDraftList(): array
    {
        $componentAccessToken = $this->platform->getComponentAccessToken();
        $api = "https://api.weixin.qq.com/wxa/gettemplatedraftlist?access_token={$componentAccessToken}";

        return HttpClient::getInstance()
            ->get($api);

    }

    /**
     * 获取代码模版库中的所有小程序代码模版
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getTemplateList(): array
    {
        $componentAccessToken = $this->platform->getComponentAccessToken();

        $api = "https://api.weixin.qq.com/wxa/gettemplatelist?access_token={$componentAccessToken}";
        return HttpClient::getInstance()
            ->get($api);

    }

    /**
     * 将草稿箱的草稿选为小程序代码模版
     *
     * @param mixed $draftId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function addToTemplate($draftId): array
    {
        $componentAccessToken = $this->platform->getComponentAccessToken();;
        $api = "https://api.weixin.qq.com/wxa/addtotemplate?access_token={$componentAccessToken}";
        $data = [
            'draft_id' => $draftId
        ];

        return HttpClient::getInstance()
            ->post($api, $data);

    }

    /**
     * 删除指定小程序代码模版
     *
     * @param mixed $templateId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function deleteTemplate($templateId): array
    {
        $componentAccessToken = $this->platform->getComponentAccessToken();;
        $api = "https://api.weixin.qq.com/wxa/deletetemplate?access_token={$componentAccessToken}";
        $data = [
            'template_id' => $templateId
        ];

        return HttpClient::getInstance()
            ->post($api, $data);

    }
}