<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform\Exception;

class ComponentVerifyTicketException extends WechatPlatformException
{
    public function __construct()
    {
        parent::__construct(
            '验证票据不存在，在第三方平台创建审核通过后，微信服务器会向其 ”授权事件接收URL” 每隔 10 分钟以 POST 的方式推送 component_verify_ticket，请做好保存'
        );
    }
}