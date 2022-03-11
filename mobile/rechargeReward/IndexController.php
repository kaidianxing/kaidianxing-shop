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

namespace shopstar\mobile\rechargeReward;

use apps\rechargeReward\base\RechargeRewardClientApiController;
use shopstar\bases\controller\BaseMobileApiController;
use shopstar\exceptions\rechargeReward\RechargeRewardException;
use shopstar\models\rechargeReward\RechargeRewardActivityModel;

/**
 * 充值优惠
 * Class IndexController
 * @package apps\rechargeReward\client
 */
class IndexController extends BaseMobileApiController
{
    /**
     * 获取优惠活动
     * @throws RechargeRewardException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetActivity()
    {
        $activity = RechargeRewardActivityModel::getOpenActivity($this->clientType, $this->memberId);
        if (is_error($activity)) {
            throw new RechargeRewardException(RechargeRewardException::ACTIVITY_NOT_EXISTS);
        }
        return $this->result(['data' => $activity]);
    }

}