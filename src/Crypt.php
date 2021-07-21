<?php

namespace XinFox\WechatPlatform;

class Crypt
{
    private string $token;
    private string $appId;

    private Prpcrypt $prpcrypt;

    public function __construct(string $appId, string $token, string $encryptKey)
    {
        $this->token = $token; // 公众平台上，开发者设置的token
        $this->appId = $appId; // 公众平台的appId

        $this->prpcrypt = new Prpcrypt($encryptKey);
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
        $nonce = Util::randomStr(16);

        $encrypt = $this->prpcrypt->encrypt($plaintext, $this->appId);

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
     * @return array
     */
    public function decrypt(string $ciphertext): array
    {
        $xmlText = $this->prpcrypt->decrypt($ciphertext);

        return XMLParse::extract($xmlText);
    }

    /**
     *验证URL
     *@param sMsgSignature: 签名串，对应URL参数的msg_signature
     *@param sTimeStamp: 时间戳，对应URL参数的timestamp
     *@param sNonce: 随机串，对应URL参数的nonce
     *@param sEchoStr: 随机串，对应URL参数的echostr
     *@param sReplyEchoStr: 解密之后的echostr，当return返回0 时有效
     *@return：成功0，失败返回对应的错误码
     */
    public function verifyURL($sMsgSignature, $sTimeStamp, $sNonce, $sEchoStr, &$sReplyEchoStr)
    {
        $signature = Sha1::sign($sEchoStr, $this->token, $sTimeStamp, $sNonce);

        if ($signature != $sMsgSignature) {
            throw new \Exception("签名错误");
        }

        $result = $this->prpcrypt->decrypt($sEchoStr, $this->appId);

        $sReplyEchoStr = $result;

        return 0;
    }
}