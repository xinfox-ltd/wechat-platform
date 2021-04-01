<?php

namespace XinFox\WechatPlatform\Miniprogram;

use XinFox\WechatPlatform\Api\ThirdPartyPlatform;
use XinFox\WechatPlatform\Exception;
use XinFox\WechatPlatform\Http;

class CodeTemplate
{
    /**
     * 获取草稿箱内的所有临时代码草稿
     *
     * @return array
     * @throws Exception
     */
    public static function getTemplateDraftList()
    {
        $componentAccessToken = ThirdPartyPlatform::getInstance()
            ->getComponentAccessToken();

        $api = "https://api.weixin.qq.com/wxa/gettemplatedraftlist?access_token={$componentAccessToken}";
        $resultData = Http::getInstance()
            ->get($api);

        return $resultData;

    }

    /**
     * 获取代码模版库中的所有小程序代码模版
     *
     * @return array
     * @throws Exception
     */
    public static function getTemplateList()
    {
        $componentAccessToken = ThirdPartyPlatform::getInstance()
            ->getComponentAccessToken();

        $api = "https://api.weixin.qq.com/wxa/gettemplatelist?access_token={$componentAccessToken}";
        $resultData = Http::getInstance()
            ->get($api);

        return $resultData;

    }

    /**
     * 将草稿箱的草稿选为小程序代码模版
     *
     * @param mixed $draftId
     * @return array
     * @throws Exception
     */
    public static function addToTemplate($draftId)
    {
        $componentAccessToken = ThirdPartyPlatform::getInstance()
            ->getComponentAccessToken();

        $api = "https://api.weixin.qq.com/wxa/addtotemplate?access_token={$componentAccessToken}";
        $data = [
            'draft_id' => $draftId
        ];

        $resultData = Http::getInstance()
            ->post($api, $data);

        return $resultData;

    }

    /**
     * 删除指定小程序代码模版
     *
     * @param mixed $templateId
     * @return array
     * @throws Exception
     */
    public static function deleteTemplate($templateId)
    {
        $componentAccessToken = ThirdPartyPlatform::getInstance()
            ->getComponentAccessToken();

        $api = "https://api.weixin.qq.com/wxa/deletetemplate?access_token={$componentAccessToken}";
        $data = [
            'template_id' => $templateId
        ];

        $resultData = Http::getInstance()
            ->post($api, $data);

        return $resultData;

    }
}