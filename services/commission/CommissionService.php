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

namespace shopstar\services\commission;

use shopstar\bases\service\BaseService;
use shopstar\constants\commission\CommissionRelationLogConstant;
use shopstar\models\commission\CommissionOrderModel;
use shopstar\models\commission\CommissionRelationModel;

/**
 * @author 青岛开店星信息技术有限公司
 */
class CommissionService extends BaseService
{

    /**
     * 订单创建处理
     * @param int $orderId
     * @param int $memberId
     * @return void
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function orderCreate(int $orderId, int $memberId): void
    {
        // 上下线关系处理
        CommissionRelationModel::handle($memberId);

        // 计算佣金
        CommissionOrderService::calculate($orderId, false);

        // 上级分销商升级
        CommissionLevelService::agentUpgrade($memberId);
    }

    /**
     * 订单支付处理
     * @param int $orderId
     * @param int $memberId
     * @return void
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function orderPay(int $orderId, int $memberId): void
    {
        // 上下线关系处理
        CommissionRelationModel::handle($memberId, 0, CommissionRelationLogConstant::TYPE_BIND);

        // 计算佣金
        CommissionOrderService::calculate($orderId, true);

        // 成为分销商
        CommissionAgentService::register($memberId);

        // 上级分销商升级
        CommissionLevelService::agentUpgrade($memberId);
    }

    /**
     * 订单完成处理
     * @param int $orderId
     * @param int $memberId
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function orderFinish(int $orderId, int $memberId): void
    {
        // 处理分销订单相关表 状态
        CommissionOrderModel::updateOrderFinish($orderId);

        // 成为分销商
        CommissionAgentService::register($memberId);

        // 上级分销商升级
        CommissionLevelService::agentUpgrade($memberId);
    }
}