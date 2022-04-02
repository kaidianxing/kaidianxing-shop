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
use shopstar\components\notice\config\SmsConfig;
use shopstar\helpers\QueueHelper;
use shopstar\jobs\components\noticeComponents\SmsMessageJob;
use shopstar\models\member\MemberModel;
use shopstar\models\notice\NoticeSmsTemplateModel;
use yii\helpers\Json;

/**
 * 短信
 * Class SmsMessage
 * @author 青岛开店星信息技术有限公司
 */
class SendSmsNotice extends SendBase implements SendNoticeInterface
{
    /**
     * 发送数据
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $messageData;

    /**
     * 短信配置
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $sms;

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
    public $options;

    /**
     * 要发送的用户
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $toUser;

    /**
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $config;

    /**
     * @throws \Exception
     */
    public function sendBefore(): bool
    {
        $this->sms = NoticeSmsTemplateModel::find()->where(['id' => $this->templateId])->one();

        if (empty($this->sms)) {
            throw new \Exception('Sms Notice Template Not Found');
        }

        $config = SmsConfig::getConfig([
            'type' => $this->sms->type,
            'sms_sign' => $this->sms->sms_sign
        ]);
        if (is_error($config)) {
            throw new \Exception($config['message']);
        }

        $this->config = $config;


        return true;
    }

    /**
     * 组成模板所需字段
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function makeTemplateField()
    {
        $content = Json::decode($this->sms->data);

        if (isset($content['data'])) {
            $content = [$content['data']];
        }

        $this->messageData = $this->makeTpl(Json::encode($content), $this->messageData);
        foreach ($this->messageData as &$item) {
            if (mb_strlen($item) > 19) {
                $item = mb_substr($item, 0, 17) . '...';
            }
        }

        return true;
    }

    /**
     * 组成发送会员
     * @param array $toUser
     * @param bool $appointTemplateMember
     * @return bool
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
                        $member = MemberModel::find()->where(['id' => $this->options['member_id']])->select('mobile')->get();
                        if ($member) {
                            $this->toUser = $member;
                        }
                    }
                } else {
                    // 如果通知等级小于订单上的等级  不发
                    if ($toUser['commission_level'] >= $this->options['commission_level']) {
                        $member = MemberModel::find()->where(['id' => $this->options['member_id']])->select('mobile')->get();
                        if ($member) {
                            $this->toUser = $member;
                        }
                    }
                }
            } else {
                $member = MemberModel::find()->where(['id' => $toUser['member_id']])->select('mobile')->get();
                if ($member) {
                    $this->toUser = $member;
                }
            }
        } else {
            $this->toUser = $toUser;
        }
        if (empty($this->toUser)) {
            return false;
        }

        return true;
    }

    /**
     * 消息发送
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function sendMessage()
    {
        foreach ((array)$this->toUser as $item) {
            if (empty($item)) {
                continue;
            }
            QueueHelper::push(new SmsMessageJob([
                'job' => [
                    'mobile' => $item['mobile'],
                    'config' => $this->config,
                    'template_id' => $this->sms->sms_tpl_id,
                    'data' => $this->messageData,
                ]
            ]));
        }
    }
}
