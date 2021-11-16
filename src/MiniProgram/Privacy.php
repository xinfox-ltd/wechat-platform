<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */

declare(strict_types=1);

namespace XinFox\WechatPlatform\MiniProgram;

use XinFox\WechatPlatform\AbstractApi;
use XinFox\WechatPlatform\HttpClient;

class Privacy extends AbstractApi
{
    /**
     * 配置小程序用户隐私保护指引
     * https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/privacy_config/set_privacy_setting.html
     *
     * @param string $authorizerAppId
     * @param array $ownerSetting
     * @param array $settingList
     * @param integer $privacyVer
     * @return array
     */
    public function setPrivacy(string $authorizerAppId, array $ownerSetting, array $settingList = [], int $privacyVer = 2): array
    {
        // 要收集的用户信息配置，可选择的用户信息类型参考下方详情。当privacy_ver传2或者不传是必填；当privacy_ver传1时，该参数不可传，否则会报错
        if ($privacyVer == 2 || empty($privacyVer)) {
            if (empty($settingList)) {
                throw new \Exception('缺少setting_list参数');
            }
        }

        // ownner_setting 结构体
        // https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/privacy_config/set_privacy_setting.html#owner-setting-%E7%BB%93%E6%9E%84%E4%BD%93
        if (!isset($ownerSetting['notice_method']) || empty($ownerSetting['notice_method'])) {
            throw new \Exception('owner_setting参数缺少notice_method字段');
        }

        $accessToken = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = 'https://api.weixin.qq.com/cgi-bin/component/setprivacysetting?access_token=' . $accessToken;
        $data = [
            'privacy_ver' => $privacyVer,
            'owner_setting' => $ownerSetting, // 注意，传参时注意不要json_encode中文变成了unicode编码，否则在小程序端会展示成乱码。建议set成功之后，调get接口看下设置的中文是否正常被显示。
            'setting_list' => $settingList,
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 查询小程序用户隐私保护指引
     * https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/privacy_config/get_privacy_setting.html
     * @param string $authorizerAppId
     * @param integer $privacyVer
     * @return array
     */
    public function getPrivacy(string $authorizerAppId, int $privacyVer = 2): array
    {

        $accessToken = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = 'https://api.weixin.qq.com/cgi-bin/component/getprivacysetting?access_token=' . $accessToken;
        $data = [
            'privacy_ver' => $privacyVer
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }

    /**
     * 上传小程序用户隐私保护指引
     *
     * @param string $authorizerAppId
     * @param file   只支持传txt文件
     * @return array
     */
    public function uploadPrivacy(string $authorizerAppId, $file): array
    {

        $accessToken = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $api = 'https://api.weixin.qq.com/cgi-bin/component/uploadprivacyextfile?access_token=' . $accessToken;
        $data = [
            'file' => $file
        ];

        return HttpClient::getInstance()
            ->post($api, $data);
    }
}
