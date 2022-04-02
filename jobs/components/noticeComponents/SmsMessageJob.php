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

use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\LogHelper;
use yii\base\BaseObject;

/**
 * @author 青岛开店星信息技术有限公司
 */
class SmsMessageJob extends BaseObject implements \yii\queue\JobInterface
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
        echo '执行手机号:' . $this->job['mobile'] . '执行时间：' . DateTimeHelper::now();

        $data = $this->job;

        try {
            $easySms = new EasySms($data['config']);
            $easySms->send($data['mobile'], [
                'content' => '',
                'template' => $data['template_id'],
                'data' => $data['data'],
            ]);
        } catch (NoGatewayAvailableException $exception) {
            LogHelper::error('[MOBILE CORD ERROR2]', $exception->getExceptions()['aliyun']->getMessage());
            echo '短信发送失败: ' . $exception->getExceptions()['aliyun']->getMessage();
            return false;
        }

        echo("短信发送成功\n");
        return true;
    }
}
