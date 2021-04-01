<?php

namespace XinFox\WechatPlatform;

use RuntimeException;

class Prpcrypt
{
    public $key;

    function __construct($key)
    {
        $this->key = base64_decode($key . "=");
    }

    /**
     * 对明文进行加密
     * @param string $plaintext 需要加密的明文
     * @param string $appId
     *
     * @return string 加密后的密文
     */
    public function encrypt(string $plaintext, string $appId): string
    {
        // 获得16位随机字符串，填充到明文之前
        $random = $this->randomStr(16);
        $plaintext = $random . pack("N", strlen($plaintext)) . $plaintext . $appId;

        $iv = substr($this->key, 0, 16);
        //使用自定义的填充方式对明文进行补位填充
        $plaintext = (new PKCS7Encoder)->encode($plaintext);
        return openssl_encrypt(
            $plaintext,
            'AES-256-CBC',
            substr($this->key, 0, 32),
            OPENSSL_ZERO_PADDING,
            $iv
        );
    }

    /**
     * 对密文进行解密
     * @param string $ciphertext 需要解密的密文
     *
     * @return string 解密得到的明文
     * @throws RuntimeException
     */
    public function decrypt(string $ciphertext): string
    {
        $iv = substr($this->key, 0, 16);
        $plaintext = openssl_decrypt(
            $ciphertext,
            'AES-256-CBC',
            substr($this->key, 0, 32),
            OPENSSL_ZERO_PADDING,
            $iv
        );

        //去除补位字符
        $PKCS7Encoder = new PKCS7Encoder;
        $plaintext = $PKCS7Encoder->decode($plaintext);
        //去除16位随机字符串,网络字节序和AppId
        if (strlen($plaintext) < 16) {
            throw new RuntimeException('解密内容为空');
        }

        $plaintext = substr($plaintext, 16, strlen($plaintext));

        $lenList = unpack("N", substr($plaintext, 0, 4));
        return substr($plaintext, 4, $lenList[1]);
    }

    /**
     * @param $length
     * @return string
     */
    protected function randomStr($length): string
    {
        //字符组合
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len = strlen($str) - 1;
        $randStr = '';
        for ($i = 0; $i < $length; $i++) {
            $num = mt_rand(0, $len);
            $randStr .= $str[$num];
        }

        return $randStr;
    }
}