<?php

use yii\db\Migration;

/**
 * Class m220830_071935_credit_shop_order
 */
class m220830_071935_credit_shop_order extends Migration
{
    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'credit_shop_order';
    }

    /**
     * 更新
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function safeUp(): bool
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '发送的优惠券id  为了回收',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单id',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '订单状态',
  `pay_credit` int(11) NOT NULL DEFAULT '0' COMMENT '支付积分',
  `pay_price` decimal(10,2) NOT NULL COMMENT '支付金额',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `option_id` int(11) NOT NULL DEFAULT '0' COMMENT '规格id',
  `shop_goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商城商品id',
  `shop_option_id` int(11) NOT NULL DEFAULT '0' COMMENT '商城规格id',
  `total` int(11) NOT NULL DEFAULT '0' COMMENT '购买数量',
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品类型  0商品  1优惠券',
  `member_coupon_id` text COMMENT '发送的优惠券id  为了回收',
  `credit_unit` int(11) NOT NULL DEFAULT '0' COMMENT '单价',
  `client_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '客户端类型',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_order` (`order_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='应用-积分商城-订单';");

        return true;
    }

    /**
     * 回滚
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function safeDown(): bool
    {
        // 删除表
        $this->dropTable($this->getTableName());

        echo "m220830_071935_credit_shop_order cannot be reverted.\n";

        return true;
    }
}
