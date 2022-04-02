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

namespace shopstar\mobile\consumeReward;

use shopstar\bases\controller\BaseMobileApiController;
use shopstar\exceptions\consumeReward\ConsumeRewardException;
use shopstar\helpers\RequestHelper;
use shopstar\models\consumeReward\ConsumeRewardActivityModel;
use shopstar\models\consumeReward\ConsumeRewardLogModel;
use shopstar\models\sale\CouponModel;
use yii\helpers\Json;

/**
 * 消费奖励
 * Class IndexController
 * @package shopstar\mobile\consumeReward
 * @author 青岛开店星信息技术有限公司
 */
class IndexController extends BaseMobileApiController
{

    /**
     * 发送奖励
     * @throws ConsumeRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSendReward()
    {

        $orderId = RequestHelper::get('order_id');
        if (empty($orderId)) {
            throw new ConsumeRewardException(ConsumeRewardException::SEND_REWARD_PARAMS_ERROR);
        }
        // 查找获取记录
        $log = ConsumeRewardLogModel::find()
            ->where(['member_id' => $this->memberId, 'order_id' => $orderId, 'is_view' => 0, 'is_finish' => 1])
            ->one();
        if (empty($log)) {
            // 无领取记录
            throw new ConsumeRewardException(ConsumeRewardException::SEND_REWARD_LOG_NOT_EXISTS);
        }

        // 获取活动
        $activity = ConsumeRewardActivityModel::find()->where(['id' => $log->activity_id])->first();
        if (empty($activity)) {
            throw new ConsumeRewardException(ConsumeRewardException::SEND_REWARD_ACTIVITY_NOT_EXISTS);
        }

        // 验证客户端类型
        $activityClientType = explode(',', $activity['client_type']);
        if (!in_array($this->clientType, $activityClientType)) {
            throw new ConsumeRewardException(ConsumeRewardException::SEND_REWARD_ACTIVITY_CLIENT_TYPE_ERROR);
        }

        $rewardInfo = Json::decode($log->reward);

        if (!$rewardInfo['reward']) {
            $activity['reward_array'] = explode(',', $activity['reward']);
        } else {
            $activity['reward_array'] = $rewardInfo['reward'];
        }

        if ($rewardInfo['balance']) {
            $activity['balance'] = $rewardInfo['balance'];
        }

        if ($rewardInfo['credit']) {
            $activity['credit'] = $rewardInfo['credit'];
        }

        // 获取优惠券
        if (in_array('1', $activity['reward_array'])) {

            if ($rewardInfo['coupon_ids']) {
                $activity['coupon_ids_array'] = explode(',', $rewardInfo['coupon_ids']);
            } else {
                $activity['coupon_ids_array'] = explode(',', $activity['coupon_ids']);
            }

            $coupons = CouponModel::getCouponInfo($activity['coupon_ids_array']);
            if (!empty($coupons)) {
                $activity['coupon_info'] = array_values($coupons);
            }
        }

        if ($rewardInfo['red_package']) {
            $activity['red_package'] = $rewardInfo['red_package'];
        }

        $log->is_view = 1;
        $log->save();

        $activity['log_id'] = $log->id;

        return $this->result($activity);
    }
}