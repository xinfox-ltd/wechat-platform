<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */

declare(strict_types=1);

namespace XinFox\WechatPlatform\MiniProgram;

use XinFox\WechatPlatform\AbstractApi;
use XinFox\WechatPlatform\HttpClient;

class InterfaceApply extends AbstractApi
{
    /**
     * 接口申请
     * https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/apply_api/apply_privacy_interface.html
     * @param  string 商户appid
     * @param  string 申请的api英文名，例如wx.choosePoi，严格区分大小写
     * @param  string 申请说原因，不超过300个字符；需要以utf-8编码提交，否则会出现审核失败
     * @param  array  (辅助网页)例如，上传官网网页链接用于辅助审核
     * @param  array  (辅助图片)填写图片的url ，最多10个
     * @param  array  (辅助视频)填写视频的链接 ，最多支持1个；视频格式只支持mp4格式
     * @return array
     */
    public function setInterface(string $authorizerAppId, string $apiName, string $content, array $urlList = [], array $picList = [], array $videoList = []): array
    {
        $accessToken = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = 'https://api.weixin.qq.com/wxa/security/apply_privacy_interface?access_token=' . $accessToken;
        $data = [
            'api_name' => $apiName,
            'content' => $content,
            'url_list' => $urlList,
            'pic_list' => $picList,
            'video_list' => $videoList,
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 获取接口列表
     * https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/apply_api/get_privacy_interface.html
     * @param  string 商户appid
     * @return array
     */
    public function getInterface(string $authorizerAppId): array
    {
        $accessToken = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = 'https://api.weixin.qq.com/wxa/security/get_privacy_interface?access_token=' . $accessToken;

        return HttpClient::getInstance()
            ->get($api);
    }
}
