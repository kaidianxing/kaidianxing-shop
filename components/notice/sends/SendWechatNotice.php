<?php

/**
 * 开店星新零售管理系统
 * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开
 * @author 青岛开店星信息技术有限公司
 * @link https://www.kaidianxing.com
 * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.
 * @copyright 版权归青岛开店星信息技术有限公司所有
 * @warning Unauthorized deletion of copyright information is prohibited.
 * @warning 未经许可禁止私自删除版权信息
 */


namespace shopstar\components\notice\sends;

use shopstar\components\notice\bases\SendBase;
use shopstar\helpers\QueueHelper;
use shopstar\jobs\components\noticeComponents\WechatMessageJob;
use shopstar\models\member\MemberModel;
use shopstar\models\member\MemberWechatModel;
use shopstar\models\notice\NoticeWechatTemplateModel;

/**
 * 公众号消息发送
 * Class SendWechatNotice
 * @author 青岛开店星信息技术有限公司
 * @package shopstar\components\notice\sends
 */
class SendWechatNotice extends SendBase implements SendNoticeInterface
{
    /**
     * 发送数据
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $messageData;

    /**
     * 模板id
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $templateId;

    /**
     * 附加参数
     * @author 青岛开店星信息技术有限公司
     * @var array
     */
    public $options = [];

    /**
     * 模板
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $template;

    /**
     * 要发送的用户
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $toUser;

    /**
     * 发送前处理（包括参数验证，模板验证）
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function sendBefore(): bool
    {

        if (empty($this->templateId) || empty($this->messageData)) {
            throw new \Exception('Wechat Notice Lack Params');
        }
        $this->template = NoticeWechatTemplateModel::findOne(['id' => $this->templateId]);
        if (empty($this->template)) {
            throw new \Exception('Wechat Notice Template Not Found');
        }

        return true;
    }

    /**
     * 组成模板所需字段
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function makeTemplateField()
    {
        $this->messageData = $this->makeWechatTpl($this->template->content, $this->messageData);

        $messageData = [];
        foreach ($this->messageData as $messageDataIndex => $messageDataItem) {
            $messageData[$messageDataItem['key']] = $messageDataItem['value'];
        }

        //重新赋值 messagedata
        $this->messageData = $messageData;

        return;
    }

    /**
     * 组成发送会员
     * @param array $toUser 会员信息
     * @param bool $appointTemplateMember 是否是指定会员
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function makeToUser(array $toUser, bool $appointTemplateMember)
    {
        //是否是模板指定会员
        if ($appointTemplateMember) {
            // 兼容特殊情况  分销 新增下级 和 下级付款
            $commissionNotice = [
                'commission_buyer_agent_add_child',
                'commission_buyer_child_pay',
                'commission_buyer_agent_add_child_line',
            ];
            if (in_array($this->options['type_code'], $commissionNotice)) {
                // 如果是内购
                if ($this->options['is_self_buy']) {
                    // 如果通知等级小于订单上的等级-1  发 不然不发
                    if ($toUser['commission_level'] >= $this->options['commission_level'] - 1) {
                        $member = MemberWechatModel::find()->where(['member_id' => $this->options['member_id']])->select('openid')->column();
                        if ($member) {
                            $this->toUser = $member;
                        }
                    }
                } else {
                    // 如果通知等级小于订单上的等级  不发
                    if ($toUser['commission_level'] >= $this->options['commission_level']) {
                        $member = MemberWechatModel::find()->where(['member_id' => $this->options['member_id']])->select('openid')->column();
                        if ($member) {
                            $this->toUser = $member;
                        }
                    }
                }
            } else {
                $this->toUser = MemberWechatModel::find()->where(['member_id' => $toUser['member_id']])->select('openid')->asArray()->column();
        
            }
        } else {
            $this->toUser = array_column(array_column($toUser, 'wechatMember'), 'openid');
        }

        if (empty($this->toUser)) {
            return false;
        }

        return true;
    }

    /**
     * 消息发送（主要处理发送，最好不要有别的操作）
     * @return mixed
     * @author 青岛开店星信息技术有限公司
     */
    public function sendMessage()
    {
        foreach ((array)$this->toUser as $item) {
            if (empty($item)) {
                continue;
            }

            QueueHelper::push(new WechatMessageJob([
                'job' => [
                    'touser' => $item,
                    'template_id' => $this->template->template_id,
                    'data' => $this->messageData,
                    'url' => $this->options['url'] ?: '',
                    'miniprogram' => [
                        'appid' => $this->options['app_id'],
                        'pagepath' => $this->options['page_path']
                    ]
                ]
            ]));
        }


        return true;
    }
}
