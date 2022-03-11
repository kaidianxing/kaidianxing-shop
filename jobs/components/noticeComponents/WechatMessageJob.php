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

use shopstar\components\wechat\helpers\OfficialAccountMessageHelper;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LogHelper;
use yii\base\BaseObject;

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
        echo '公众号消息执行时间：' . DateTimeHelper::now();

        $data = $this->job;
        try {
            $result = OfficialAccountMessageHelper::send( [
                'touser' => $data['touser'],
                'template_id' => $data['template_id'],
                'url' => $data['url'] ?: '',
                'miniprogram' => [
                    'appid' => $data['miniprogram']['appid'] ?: '',
                    'pagepath' => $data['miniprogram']['pagepath'] ?: '',
                ],
                'data' => $data['data'],
            ]);

            if (is_error($result)) {
                throw new \Exception($result['message']);
            }
        } catch (\Throwable $throwable) {
            LogHelper::error('WECHAT NOTICE SEND ERROR',[
                'message' => $throwable->getMessage()
            ]);

            echo '微信公众号发送失败: ' . $throwable->getMessage() . "\n";
            return false;
        }

        echo("微信公众号发送成功,时间:" . date('Y-m-d H:i:s') . "\n");
        return true;
    }
}
