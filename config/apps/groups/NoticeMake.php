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

namespace shopstar\config\apps\groups;

use shopstar\components\notice\bases\BaseMake;

/**
 * 拼团字段转化字段
 * Class NoticeMake
 * @package shopstar\config\apps\groups
 * @author likexin
 */
class NoticeMake extends BaseMake
{

    /**
     * 预留字段,用于转化
     * @var string[]
     * @author Jason
     */
    public $reserveField = [
        'goods_title' => '[商品名称]',
        'goods_info' => '[商品]',
        'groups_goods' => '[拼团商品]',
        'price_unit' => '[商品金额]',
        'pay_price' => '[支付金额]',
        'refund_price' => '[退款金额]',
        'team_no' => '[拼团订单编号]',
        'groups_member_nickname' => '[拼团人员]',
        'groups_member_nickname_all' => '[拼团成员]',
        'groups_price' => '[拼团金额]',
        'send_time' => '[发货时间]',
        'groups_member_num' => '[成团人数]',
        'groups_end_time' => '[活动结束时间]',
    ];

}