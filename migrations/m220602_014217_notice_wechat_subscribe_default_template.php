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

use yii\db\Migration;

/**
 * 公众号订阅消息默认模板
 * Class m220602_014217_notice_wechat_subscribe_default_template
 * @author likexin
 */
class m220602_014217_notice_wechat_subscribe_default_template extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'notice_wechat_subscribe_default_template';
    }

    /**
     * 更新
     * {@inheritdoc}
     * @author likexin
     */
    public function safeUp()
    {
        // 创建表
        $this->execute("CREATE TABLE IF NOT EXISTS  `{$this->getTableName()}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '名称',
  `scene_code` varchar(191) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '类型编码',
  `template_id` varchar(191) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '模板编码',
  `template_name` varchar(191) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '模板名称',
  `kid_list` varchar(191) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '关键词顺序',
  `scene_desc` varchar(191) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '服务场景描述',
  `content` mediumtext CHARACTER SET utf8 NOT NULL COMMENT '内容',
  PRIMARY KEY (`id`),
  KEY `type_code` (`scene_code`),
  KEY `template_code` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='消息通知-公众号一次性订阅消息-默认模板';");

        // 添加默认数据
        $this->execute("INSERT INTO `{$this->getTableName()}` (`id`, `name`, `scene_code`, `template_id`, `template_name`, `kid_list`, `scene_desc`, `content`) VALUES (1, '买家订单发货通知', 'buyer_order_send', '4498', '订单进度提醒', '[2,5,1]', '买家订单发货通知', '[{\"key\":\"character_string2\",\"value\":\"[订单编号]\"},{\"key\":\"phrase5\",\"value\":\"待收货\"},{\"key\":\"thing1\",\"value\":\"您的订单已发货，请注意查收\"}]');");
        $this->execute("INSERT INTO `{$this->getTableName()}` (`id`, `name`, `scene_code`, `template_id`, `template_name`, `kid_list`, `scene_desc`, `content`) VALUES (2, '买家订单支付通知', 'buyer_order_pay', '1253', '订单支付成功通知', '[1,5,2,6]', '买家订单支付通知', '[{\"key\":\"character_string1\",\"value\":\"[订单编号]\"},{\"key\":\"phrase5\",\"value\":\"待发货\"},{\"key\":\"date2\",\"value\":\"[支付时间]\"},{\"key\":\"thing6\",\"value\":\"您的订单已支付成功，感谢使用\"}]');");
        $this->execute("INSERT INTO `{$this->getTableName()}` (`id`, `name`, `scene_code`, `template_id`, `template_name`, `kid_list`, `scene_desc`, `content`) VALUES (3, '订单手动退款通知', 'buyer_order_cancel_and_refund', '5049', '售后状态通知', '[2,12,1]', '订单手动退款通知', '[{\"key\":\"character_string2\",\"value\":\"[订单编号]\"},{\"key\":\"amount12\",\"value\":\"[退款金额]\"},{\"key\":\"thing1\",\"value\":\"商家已退款，请注意查收\"}]');");
        $this->execute("INSERT INTO `{$this->getTableName()}` (`id`, `name`, `scene_code`, `template_id`, `template_name`, `kid_list`, `scene_desc`, `content`) VALUES (4, '买家提现成功通知', 'buyer_pay_withdraw', '33287', '提现状态通知', '[1,2,3,5]', '买家提现成功通知', '[{\"key\":\"thing1\",\"value\":\"[会员昵称]\"},{\"key\":\"amount2\",\"value\":\"[提现金额]\"},{\"key\":\"phrase3\",\"value\":\"提现成功\"},{\"key\":\"thing5\",\"value\":\"请注意查收\"}]');");
        $this->execute("INSERT INTO `{$this->getTableName()}` (`id`, `name`, `scene_code`, `template_id`, `template_name`, `kid_list`, `scene_desc`, `content`) VALUES (5, '买家充值成功通知', 'buyer_pay_recharge', '1972', '余额变动提醒', '[1,4,5]', '买家充值成功通知', '[{\"key\":\"amount1\",\"value\":\"[充值金额]\"},{\"key\":\"thing4\",\"value\":\"[充值方式]\"},{\"key\":\"thing5\",\"value\":\"余额已到账，请注意查收\"}]');");
        $this->execute("INSERT INTO `{$this->getTableName()}` (`id`, `name`, `scene_code`, `template_id`, `template_name`, `kid_list`, `scene_desc`, `content`) VALUES (6, '买家下级付款通知', 'commission_buyer_child_pay', '26699', '佣金待入账通知', '[1,2,5]', '买家下级付款通知', '[{\"key\":\"character_string1\",\"value\":\"[订单编号]\"},{\"key\":\"amount2\",\"value\":\"[订单金额]\"},{\"key\":\"amount5\",\"value\":\"[佣金金额]\"}]');");
        $this->execute("INSERT INTO `{$this->getTableName()}` (`id`, `name`, `scene_code`, `template_id`, `template_name`, `kid_list`, `scene_desc`, `content`) VALUES (7, '提现申请失败通知', 'commission_buyer_withdraw_apply_fail', '33287', '提现状态通知', '[2,3,5]', '提现申请失败通知', '[{\"key\":\"amount2\",\"value\":\"[提现金额]\"},{\"key\":\"phrase3\",\"value\":\"审核拒绝\"},{\"key\":\"thing5\",\"value\":\"您的佣金提现申请未通过\"}]');");
        $this->execute("INSERT INTO `{$this->getTableName()}` (`id`, `name`, `scene_code`, `template_id`, `template_name`, `kid_list`, `scene_desc`, `content`) VALUES (8, '买家佣金打款通知', 'commission_buyer_commission_pay', '2001', '提现成功通知', '[7,1,6]', '买家佣金打款通知', '[{\"key\":\"thing7\",\"value\":\"[用户昵称]\"},{\"key\":\"amount1\",\"value\":\"[提现金额]\"},{\"key\":\"thing6\",\"value\":\"您的佣金已成功打款，请注意查收\"}]');");
        $this->execute("INSERT INTO `{$this->getTableName()}` (`id`, `name`, `scene_code`, `template_id`, `template_name`, `kid_list`, `scene_desc`, `content`) VALUES (9, '买家优惠券发放通知', 'buyer_coupon_send', '2247', '优惠券获取提醒', '[2,7,4]', '买家优惠券发放通知', '[{\"key\":\"thing2\",\"value\":\"[优惠券类型]\"},{\"key\":\"time7\",\"value\":\"[发放时间]\"},{\"key\":\"thing4\",\"value\":\"优惠券已领取成功，请在有效期内使用\"}]');");

        return true;
    }

    /**
     * 回滚
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // 删除表
        $this->dropTable($this->getTableName());

        echo "m220602_014217_notice_wechat_subscribe_default_template cannot be reverted.\n";

        return true;
    }
}
