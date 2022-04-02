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

namespace shopstar\constants\commission;

use shopstar\bases\constant\BaseConstant;

/**
 * 分销商常量表
 * Class CommissionAgentConst
 * @method getMessage($code) static 获取文字
 * @package shopstar\constants\commission
 * @author 青岛开店星信息技术有限公司
 */
class CommissionAgentConstant extends BaseConstant
{
    // 所有分销商
    public const ALL_AGENT = 1;

    // 待审核列表
    public const WAIT_AGENT = 0;

    // 审核 拒绝
    public const AGENT_STATUS_REJECT = -1;

    // 审核 待审核
    public const AGENT_STATUS_WAIT = 0;

    // 审核 通过
    public const AGENT_STATUS_SUCCESS = 1;

    // 审核 取消
    public const AGENT_STATUS_CANCEL = -2;

    // 购买商品 成为分销条件
    public const AGENT_BECOME_CONDITION_BUY_GOODS = 1;

    // 消费金额 成为分销条件
    public const AGENT_BECOME_CONDITION_MONEY_COUNT = 2;

    // 支付订单数量 成为分销条件
    const AGENT_BECOME_CONDITION_PAY_ORDER_COUNT = 3;

    // 无条件 成为分销条件
    const AGENT_BECOME_CONDITION_NO_CONDITION = 0;

    // 申请 成为分销条件
    const AGENT_BECOME_CONDITION_APPLY = 4;

}