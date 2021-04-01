<?php

namespace XinFox\WechatPlatform;

class Sha1
{
    public static function sign($message, $token, $timestamp, $nonce)
    {
        $array = [$message, $token, $timestamp, $nonce];
        sort($array, SORT_STRING);
        $str = implode($array);

        return sha1($str);
    }
}