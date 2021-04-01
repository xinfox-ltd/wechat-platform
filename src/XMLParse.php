<?php

namespace XinFox\WechatPlatform;

class XMLParse
{
    /**
     * 提取出xml数据包中的加密消息
     * @param string $xmlText 待提取的xml字符串
     * @return array 提取出的加密消息字符串
     */
    public static function extract(string $xmlText): array
    {
        return (array)simplexml_load_string($xmlText, 'SimpleXMLElement', LIBXML_NOCDATA);
    }

    /**
     * 生成xml消息
     * @param string $encrypt 加密后的消息密文
     * @param string $signature 安全签名
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     *
     * @return string
     */
    public static function generate(string $encrypt, string $signature, string $timestamp, string $nonce): string
    {
        $format = "<xml>
<Encrypt><![CDATA[%s]]></Encrypt>
<MsgSignature><![CDATA[%s]]></MsgSignature>
<TimeStamp>%s</TimeStamp>
<Nonce><![CDATA[%s]]></Nonce>
</xml>";
        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }
}