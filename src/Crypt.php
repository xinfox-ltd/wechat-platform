<?php

namespace XinFox\WechatPlatform;

use Phalcon\Text;

class Crypt
{
    private $token;
    private $encodingAesKey;
    private $appId;

    public function __construct()
    {
        $config = container('config')->path('vendor.weixin.platform');

        $this->token = $config->token; // 公众平台上，开发者设置的token
        $this->encodingAesKey = $config->encodingAesKey; // 公众平台上，开发者设置的EncodingAESKey
        $this->appId = $config->appid; // 公众平台的appId
    }

    /**
     * 将公众平台回复用户的消息加密打包.
     * <ol>
     *    <li>对要发送的消息进行AES-CBC加密</li>
     *    <li>生成安全签名</li>
     *    <li>将消息密文和安全签名打包成xml格式</li>
     * </ol>
     *
     * @param string $plaintext 公众平台待回复用户的消息，xml格式的字符串
     *
     * @return string
     */
    public function encrypt(string $plaintext): string
    {
        $timestamp = time();
        $nonce = Text::random(Text::RANDOM_ALNUM, 16);

        $encrypt = (new Prpcrypt($this->encodingAesKey))
            ->encrypt($plaintext, $this->appId);

        $signature = Sha1::sign($encrypt, $this->token, $timestamp, $nonce);

        return XMLParse::generate(
            $encrypt,
            $signature,
            $timestamp,
            $nonce
        );
    }


    /**
     * 检验消息的真实性，并且获取解密后的明文.
     * <ol>
     *    <li>利用收到的密文生成安全签名，进行签名验证</li>
     *    <li>若验证通过，则提取xml中的加密消息</li>
     *    <li>对消息进行解密</li>
     * </ol>
     * @param string $ciphertext
     * @return string
     */
    public function decrypt(string $ciphertext): string
    {
        $crypt = new Prpcrypt($this->encodingAesKey);

        return $crypt->decrypt($ciphertext);
    }
}