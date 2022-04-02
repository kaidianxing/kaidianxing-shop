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

namespace shopstar\jobs\components\responseComponents;

use shopstar\components\wechat\helpers\OfficialAccountMediaHelper;
use shopstar\components\wechat\helpers\OfficialAccountMessageTextHelper;
use shopstar\helpers\DateTimeHelper;
use yii\base\BaseObject;

/**
 * @author 青岛开店星信息技术有限公司
 */
class WechatMessageJob extends BaseObject implements \yii\queue\JobInterface
{
    /**
     * 参数传递
     * @author 青岛开店星信息技术有限公司
     * @var
     */
    public $job;

    /**
     * @inheritDoc
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     */
    public function execute($queue)
    {
        echo '公众号推送消息执行时间：' . DateTimeHelper::now();

        $data = $this->job;

        try {
            //发送图片
            if ($data['type'] == 1) {
                OfficialAccountMessageTextHelper::sendNews($data['openid'], $data['message']);
            }

            //发送文字
            if ($data['type'] == 2) {
                OfficialAccountMessageTextHelper::sendText($data['openid'], $data['message']);
            }

            // 真正的发送图片 上面的好像是发送图文
            if ($data['type'] == 3) {
                OfficialAccountMediaHelper::sendImage($data['openid'], $data['message']);
            }

        } catch (\Throwable $throwable) {
            echo '公众号推送消息发送失败: ' . $throwable->getMessage() . "\n";
            return false;
        }

        echo("公众号推送消息发送成功,时间:" . date('Y-m-d H:i:s') . "\n");
        return true;
    }
}