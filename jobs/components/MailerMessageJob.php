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

namespace shopstar\jobs\components;

use shopstar\components\email\EmailComponent;
use shopstar\helpers\DateTimeHelper;
use shopstar\models\shop\ShopSettings;
use yii\base\BaseObject;

class MailerMessageJob extends BaseObject implements \yii\queue\JobInterface
{
    /**
     * 订单id
     * @var
     */
    public $orderId;

    /**
     * 接收人邮箱
     * @var
     */
    public $toMailer;

    /**
     * 发送内容
     * @var
     */
    public $body;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        echo '发送邮件到:' . $this->toMailer . '执行时间：' . DateTimeHelper::now();

        $settingStatus = ShopSettings::get('mailer.status');
        if ($settingStatus == 0) {
            echo '邮件设置未开启';
            return false;
        }

        try {
            EmailComponent::sendMessage($this->toMailer, $this->body);
        } catch (\Exception $exception) {
            echo '邮件发送失败: ' . $exception->getMessage();
            return false;
        }

        echo("邮件发送成功\n");
        return true;
    }
}
