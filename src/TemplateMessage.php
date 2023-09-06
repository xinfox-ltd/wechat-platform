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
     * 设置所属行业
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setIndustry(string $authorizerAppId, $industryId1, $industryId2)
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $uri = '/cgi-bin/template/api_set_industry?access_token=' . $token;

        return HttpClient::getInstance()->post($uri, ['industry_id1' => $industryId1, 'industry_id2' => $industryId2]);
    }

    /**
     * 获取设置的行业信息
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getIndustry(string $authorizerAppId, $primaryIndustry, $secondaryIndustry)
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $uri = '/cgi-bin/template/get_industry?access_token=' . $token;

        return HttpClient::getInstance()->get(
            $uri,
            ['primary_industry' => $primaryIndustry, 'secondary_industry' => $secondaryIndustry]
        );
    }

    /**
     * 添加模板
     * @param string $authorizerAppId
     * @param string $templateIdShort
     * @param array $keywordNameList
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function addTemplate(string $authorizerAppId, string $templateIdShort, array $keywordNameList = [])
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $uri = '/cgi-bin/template/api_add_template?access_token=' . $token;

        $data = ['template_id_short' => $templateIdShort];
        if (!empty($keywordNameList)) {
            $data['keyword_name_list'] = $keywordNameList;
        }

        return HttpClient::getInstance()->post($uri, $data);
    }

    /**
     * 获取模板列表
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getAllPrivateTemplate(string $authorizerAppId)
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $uri = '/cgi-bin/template/get_all_private_template?access_token=' . $token;

        return HttpClient::getInstance()->get(
            $uri
        );
    }

    /**
     * 删除模板
     * @param string $authorizerAppId
     * @param string $templateId
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \XinFox\WechatPlatform\Exception\ApiException
     * @throws \XinFox\WechatPlatform\Exception\ComponentVerifyTicketException
     */
    public function delPrivateTemplate(string $authorizerAppId, string $templateId)
    {
        $token = $this->platform->getAuthorizerAccessToken($authorizerAppId);
        $uri = '/cgi-bin/template/del_private_template?access_token=' . $token;

        return HttpClient::getInstance()->post($uri, ['template_id' => $templateId]);
    }
}
