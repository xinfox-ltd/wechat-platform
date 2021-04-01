<?php

/**
 * [XinFox System] Copyright (c) 2011 - 2021 XINFOX.CN
 */
declare(strict_types=1);

namespace XinFox\WechatPlatform;

interface AuthorizationRepository
{
    public function getOneByAuthorizerAppId(string $authorizerAppId): Authorization;

    public function save(Authorization $authorization);
}