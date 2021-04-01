<?php

namespace XinFox\WechatPlatform\Miniprogram;

use GuzzleHttp\Client;
use XinFox\WechatPlatform\Api\ThirdPartyPlatform;
use XinFox\WechatPlatform\Exception;
use XinFox\WechatPlatform\Http;

/**
 * 代码管理
 * @package XinFox\WechatPlatform\Miniprogram
 */
class Code
{
    /**
     * 为授权的小程序帐号上传小程序代码
     *
     * @param string $authorizerAppid
     * @param array $data
     * @return array
     * @throws Exception
     */
    public static function commit(string $authorizerAppid, array $data)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/commit?access_token={$token}";

        $resultData = Http::getInstance()
            ->post($api, $data);

        return $resultData;
    }

    /**
     * 获取体验小程序的体验二维码
     *
     * @param string $authorizerAppid
     * @param string|null $path
     * @return string
     * @throws Exception
     */
    public static function getExperienceQrcode(string $authorizerAppid, string $path = null)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $url = "https://api.weixin.qq.com/wxa/get_qrcode?access_token={$token}";
        if ($path !== null) {
            $url .= "&path=" . urlencode($path);
        }

        $client = new Client();
        $result = $client->get($url);

        $filePath = sprintf("%s/%s.jpg", storage_path("app/images/temp") , $authorizerAppid);
        file_put_contents($filePath, $result->getBody()->getContents());

        return container('url')->get("images/temp/{$authorizerAppid}.jpg");
    }

    /**
     * 获取授权小程序帐号已设置的类目
     *
     * @param string $authorizerAppid
     * @return array
     * @throws Exception
     */
    public static function getCategory(string $authorizerAppid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/get_category?access_token={$token}";

        $resultData = Http::getInstance()
            ->get($api);

        return $resultData;
    }

    /**
     * 获取小程序的第三方提交代码的页面配置
     *
     * @param string $authorizerAppid
     * @return array
     * @throws Exception
     */
    public static function getPage(string $authorizerAppid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/get_page?access_token={$token}";

        $resultData = Http::getInstance()
            ->get($api);

        return $resultData;
    }

    /**
     * 将第三方提交的代码包提交审核
     *
     * @param string $authorizerAppid
     * @param array $data
     * @return array
     * @throws Exception
     */
    public static function submitAudit(string $authorizerAppid, array $data)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/submit_audit?access_token={$token}";


        $resultData = Http::getInstance()
            ->post($api, $data);

        return $resultData;
    }

    /**
     * 查询某个指定版本的审核状态
     *
     * @param string $authorizerAppid
     * @param $auditid
     * @return array
     * @throws Exception
     */
    public static function getAuditstatus(string $authorizerAppid, $auditid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/get_auditstatus?access_token={$token}";
        $data = [
            'auditid' => $auditid
        ];

        $resultData = Http::getInstance()
            ->post($api, $data);

        return $resultData;
    }

    /**
     * 查询最新一次提交的审核状态
     *
     * @param string $authorizerAppid
     * @return array
     * @throws Exception
     */
    public static function getLatestAuditstatus(string $authorizerAppid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/get_latest_auditstatus?access_token={$token}";

        $resultData = Http::getInstance()
            ->get($api);

        return $resultData;
    }

    /**
     * 小程序审核撤回
     *
     * @param string $authorizerAppid
     * @return array
     * @throws Exception
     */
    public static function undoCodeAudit(string $authorizerAppid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/undocodeaudit?access_token={$token}";

        $resultData = Http::getInstance()
            ->get($api);

        return $resultData;
    }

    /**
     * 发布已通过审核的小程序
     *
     * @param string $authorizerAppid
     * @return array
     * @throws Exception
     */
    public static function release(string $authorizerAppid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/release?access_token={$token}";

        $resultData = Http::getInstance()
            ->post($api, "{}");

        return $resultData;
    }

    /**
     * 分阶段发布
     *
     * @param string $authorizerAppid
     * @param int $grayRercentage 灰度的百分比，1到100的整数
     * @return array
     * @throws Exception
     */
    public static function grayRelease(string $authorizerAppid, int $grayRercentage)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/grayrelease?access_token={$token}";
        $data = [
            'gray_percentage' => $grayRercentage,
        ];

        $resultData = Http::getInstance()
            ->post($api, $data);

        return $resultData;
    }

    /**
     * 查询当前分阶段发布详情
     *
     * @param string $authorizerAppid
     * @return array
     * @throws Exception
     */
    public static function getgrayreleaseplan(string $authorizerAppid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/getgrayreleaseplan?access_token={$token}";

        $resultData = Http::getInstance()
            ->get($api);

        return $resultData;
    }

    /**
     * 取消分阶段发布
     *
     * @param string $authorizerAppid
     * @return array
     * @throws Exception
     */
    public static function revertGrayRelease(string $authorizerAppid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/revertgrayrelease?access_token={$token}";

        $resultData = Http::getInstance()
            ->get($api);

        return $resultData;
    }

    /**
     * 修改小程序线上代码的可见状态
     *
     * @param string $authorizerAppid
     * @param string $action 设置可访问状态，发布后默认可访问，close为不可见，open为可见
     * @return array
     * @throws Exception
     */
    public static function changeVisitstatus(string $authorizerAppid, string $action)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/change_visitstatus?access_token={$token}";
        $data = [
            'action' => $action == 'close' ? 'close' : 'open'
        ];

        $resultData = Http::getInstance()
            ->post($api, $data);

        return $resultData;
    }

    /**
     * 小程序版本回退
     *
     * @param string $authorizerAppid
     * @return array
     * @throws Exception
     */
    public static function revertCodeRelease(string $authorizerAppid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/revertcoderelease?access_token={$token}";

        $resultData = Http::getInstance()
            ->get($api);

        return $resultData;
    }


    /**
     * 查询当前设置的最低基础库版本及各版本用户占比
     *
     * @param string $authorizerAppid
     * @return array
     * @throws Exception
     */
    public static function getWeappSupportVersion(string $authorizerAppid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/getweappsupportversion?access_token={$token}";
        $data = [];

        $resultData = Http::getInstance()
            ->post($api, $data);

        return $resultData;
    }

    /**
     * 设置最低基础库版本
     *
     * @param string $authorizerAppid
     * @param mixed $version
     * @return array
     * @throws Exception
     */
    public static function setWeappSupportVersion(string $authorizerAppid, $version)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/wxa/setweappsupportversion?access_token={$token}";
        $data = [
            'version' => $version
        ];

        $resultData = Http::getInstance()
            ->post($api, $data);

        return $resultData;
    }

    /**
     * ?增加或修改二维码规则
     *
     * @param string $authorizerAppid
     * @return array
     * @throws Exception
     */
    public static function qrcodeJumpAdd(string $authorizerAppid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/cgi-bin/wxopen/qrcodejumpadd?access_token={$token}";
        $data = [
            "prefix" => "https://weixin.qq.com/qrcodejump",
            "permit_sub_rule" => "1",
            "path" => "pages/index/index",
            "open_version" => "1",
            "debug_url" => [
                "https://weixin.qq.com/qrcodejump?a=1",
                "https://weixin.qq.com/qrcodejump?a=2"
            ],
            "is_edit" => 0
        ];

        $resultData = Http::getInstance()
            ->post($api, $data);

        return $resultData;
    }

    /**
     * 获取已设置的二维码规则
     *
     * @param string $authorizerAppid
     * @return array
     * @throws Exception
     */
    public static function qrcodeJumpGet(string $authorizerAppid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/cgi-bin/wxopen/qrcodejumpget?access_token={$token}";

        $resultData = Http::getInstance()
            ->post($api, []);

        return $resultData;
    }

    /**
     * 获取已设置的二维码规则
     *
     * @param string $authorizerAppid
     * @return array
     * @throws Exception
     */
    public static function qrcodeJumpDownload(string $authorizerAppid)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/cgi-bin/wxopen/qrcodejumpdownload?access_token={$token}";

        $resultData = Http::getInstance()
            ->post($api, []);

        return $resultData;
    }

    /**
     * 删除已设置的二维码规则
     *
     * @param string $authorizerAppid
     * @param string $prefix
     * @return array
     * @throws Exception
     */
    public static function qrcodeJumpDelete(string $authorizerAppid, string $prefix)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/cgi-bin/wxopen/qrcodejumpdelete?access_token={$token}";
        $data = [
            'prefix' => $prefix
        ];

        $resultData = Http::getInstance()
            ->post($api, $data);

        return $resultData;
    }

    /**
     * 发布已设置的二维码规则
     *
     * @param string $authorizerAppid
     * @param string $prefix
     * @return array
     * @throws Exception
     */
    public static function qrcodeJumpPublish(string $authorizerAppid, string $prefix)
    {
        $token = ThirdPartyPlatform::getInstance()
            ->getAuthorizerAccessToken($authorizerAppid);

        $api = "https://api.weixin.qq.com/cgi-bin/wxopen/qrcodejumppublish?access_token={$token}";
        $data = [
            'prefix' => $prefix
        ];

        $resultData = Http::getInstance()
            ->post($api, $data);

        return $resultData;
    }
}