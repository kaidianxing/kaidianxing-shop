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

namespace shopstar\jobs\sale;

use shopstar\helpers\DateTimeHelper;
use shopstar\models\sale\CouponModel;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * @author 青岛开店星信息技术有限公司
 */
class CouponExpireJob extends BaseObject implements JobInterface
{

    /**
     * @var int 优惠券id
     */
    public $couponId;

    /**
     * @param Queue $queue
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public function execute($queue)
    {
        // 查找优惠券
        $coupon = CouponModel::find()->where(['id' => $this->couponId])->first();
        if (empty($coupon)) {
            return;
        }
        // 未过期 跳过
        if ($coupon['time_limit'] != 0 || $coupon['end_time'] > DateTimeHelper::now()) {
            return;
        }

    }
}