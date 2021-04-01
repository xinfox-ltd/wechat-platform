<?php

namespace XinFox\WechatPlatform\TemplateMessages\Deliveryer;

use XinFox\WechatPlatform\TemplateMessages\Adapter;
use XinFox\WechatPlatform\TemplateMessages\AdapterInterface;

/**
 * 订单派单通知
 *
 * Class OrderDistribution
 * @package XinFox\WechatPlatform\TemplateMessages\Deliveryer
 */
class OrderDistribution extends Adapter implements AdapterInterface
{
    protected $number;

    protected $delivery_time;

    protected $user_name;

    public function __construct()
    {
        // 配送单配送提醒
        $this->id = '9dEpMoabSQZ_gFU_h8R-MTNYVvkz6Jgg5WGBGGnmqdg';
    }

    /**
     * @param integer $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @param string $arriveTime
     */
    public function setDeliveryTime($deliveryTime)
    {
        $this->delivery_time = $deliveryTime;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->user_name = $this->intercept($userName, 8);
    }

    public function getOptions()
    {
        return [
            'first'    => [
                'value' => "订单配送提醒",
                'color' => "#000000"
            ],
            'keyword1' => [
                'value' => $this->number,
                'color' => "#000000"
            ],
            'keyword2' => [
                'value' => $this->delivery_time,
                'color' => "#000000"
            ],
            'keyword3' => [
                'value' => $this->user_name,
                'color' => "#000000"
            ],
            'remark'   => [
                'value' => "点击“详情”查看订单信息",
                'color' => "#000000"
            ],
        ];
    }
}
