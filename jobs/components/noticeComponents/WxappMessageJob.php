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

namespace shopstar\jobs\components\noticeComponents;

use shopstar\components\wechat\helpers\MiniProgramSubscriptionNoticeHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LogHelper;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\JobInterface;

/**
 * @author 青岛开店星信息技术有限公司
 */
class WxappMessageJob extends BaseObject implements JobInterface
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
     * @throws \yii\base\Exception
     */
    public function execute($queue)
    {
        echo '小程序消息执行时间：' . DateTimeHelper::now();

        $data = $this->job;
        try {
            $result = MiniProgramSubscriptionNoticeHelper::send([
                'touser' => $data['touser'],
                'template_id' => $data['template_id'],
                'page' => $data['pagepath'] ?: '',
                'form_id' => $data['form_id'],
                'data' => $data['data'],
            ]);

            if (is_error($result)) {
                throw new \Exception($result['message']);
            }

        } catch (\Throwable $throwable) {
            LogHelper::error('WECHAT APP NOTICE SEND ERROR',[
                'message' => $throwable->getMessage(),
                'data' => Json::encode($data['data'])
            ]);
            echo '小程序消息发送失败: ' . $throwable->getMessage() . "\n";
            return false;
        }

        echo("小程序消息发送成功,时间:" . date('Y-m-d H:i:s') . "\n");
        return true;
    }
}
