<?php

namespace XinFox\WechatPlatform;

/**
 * Class Sha1
 * @package XinFox\WechatPlatform
 */
class Sha1
{
    /**
     * @param $message
     * @param $token
     * @param $timestamp
     * @param $nonce
     * @return string
     */
    public static function sign($message, $token, $timestamp, $nonce): string
    {
        $array = [$message, $token, $timestamp, $nonce];
        sort($array, SORT_STRING);
        $str = implode($array);

        return sha1($str);
    }
}