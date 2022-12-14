<?php

namespace XinFox\WechatPlatform;


use XinFox\WechatPlatform\TemplateMessages\TemplateMessageInterface;

class TemplateMessage extends AbstractApi
{

    public function send(TemplateMessageInterface $message)
    {
        $token = $this->platform->getAuthorizerAccessToken($message->getAppId());
        $uri = '/cgi-bin/message/template/send?access_token=' . $token;

        return HttpClient::getInstance()->post($uri, $message->toArray());
    }

    /**
     * 选用模板
     * @param string $authorizerAppId
     * @param string $tid
     * @param string $sceneDesc
     * @param array $kidList
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function addTemplate(string $authorizerAppId, string $tid, string $sceneDesc, array $kidList = [])
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $uri = '/wxaapi/newtmpl/addtemplate?access_token=' . $token;

        if (empty($kidList)) {
            $kidList = [];
            $data = $this->getPubTemplateKeyWordsById($authorizerAppId, $tid);
            foreach ($data['data'] as $datum) {
                $kidList[] = $datum['kid'];
            }
        }

        return HttpClient::getInstance()->post($uri, ['tid' => $tid, 'kidList' => $kidList, 'sceneDesc' => $sceneDesc]);
    }

    /**
     * 删除模板
     * @param string $authorizerAppId
     * @param string $priTmplId
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function deleteTemplate(string $authorizerAppId, string $priTmplId)
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $uri = '/wxaapi/newtmpl/deltemplate?access_token=' . $token;

        return HttpClient::getInstance()->post($uri, ['priTmplId' => $priTmplId]);
    }

    /**
     * 获取模板中的关键词
     * @param string $authorizerAppId
     * @param string $tid
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getPubTemplateKeyWordsById(string $authorizerAppId, string $tid)
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $uri = '/wxaapi/newtmpl/getpubtemplatekeywords?access_token=' . $token;

        return HttpClient::getInstance()->get($uri, ['tid' => $tid]);
    }

    /**
     * 获取类目下的公共模板
     * @param string $authorizerAppId
     * @param $ids
     * @param int $start
     * @param int $limit
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getPubTemplateTitleList(string $authorizerAppId, $ids, int $start = 0, int $limit = 30)
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $uri = '/wxaapi/newtmpl/getpubtemplatetitles?access_token=' . $token;

        return HttpClient::getInstance()->get($uri, ['ids' => $ids, 'start' => $start, 'limit' => $limit]);
    }

    /**
     * 获取私有模板列表
     * @param string $authorizerAppId
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function getPrivateTemplateList(string $authorizerAppId)
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $uri = '/wxaapi/newtmpl/gettemplate?access_token=' . $token;

        return HttpClient::getInstance()->get($uri);
    }
}
