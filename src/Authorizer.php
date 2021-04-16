<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform;

/**
 * Class Authorizer
 * 参数    类型    说明
 * nick_name    string    昵称
 * head_img    string    头像
 * service_type_info    object    公众号类型
 * verify_type_info    object    公众号认证类型
 * user_name    string    原始 ID
 * principal_name    string    主体名称
 * alias    string    公众号所设置的微信号，可能为空
 * business_info    object    用以了解功能的开通状况（0代表未开通，1代表已开通），详见business_info 说明
 * qrcode_url    string    二维码图片的 URL，开发者最好自行也进行保存
 * @package XinFox\WechatPlatform
 */
class Authorizer
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function geHeadImg(): string
    {
        return $this->data['head_img'];
    }

    public function getServiceTypeInfo(): array
    {
        return $this->data['service_type_info'];
    }

    public function getVerifyTypeInfo(): array
    {
        return $this->data['verify_type_info'];
    }

    public function getUserName(): string
    {
        return $this->data['user_name'];
    }

    public function getPrincipalName(): string
    {
        return $this->data['principal_name'];
    }

    public function getAlias(): ?string
    {
        return $this->data['alias'];
    }

    public function getBusinessInfo(): array
    {
        return $this->data['business_info'];
    }

    public function getQrcodeUrl(): string
    {
        return $this->data['qrcode_url'];
    }
}