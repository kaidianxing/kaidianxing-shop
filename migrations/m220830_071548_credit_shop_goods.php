<?php

use yii\db\Migration;

/**
 * Class m220830_071548_credit_shop_goods.
 */
class m220830_071548_credit_shop_goods extends Migration
{
    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'credit_shop_goods';
    }

    /**
     * 更新
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function safeUp(): bool
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品或优惠券id',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型  0 商品  1优惠券',
  `has_option` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否多规格',
  `credit_shop_credit` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `credit_shop_price` decimal(10,2) NOT NULL COMMENT '价格',
  `credit_shop_stock` int(11) NOT NULL DEFAULT '0' COMMENT '库存',
  `original_stock` int(11) NOT NULL DEFAULT '0' COMMENT '库存',
  `sale` int(11) NOT NULL DEFAULT '0' COMMENT '销量',
  `min_price_credit` int(11) NOT NULL DEFAULT '0' COMMENT '最小价格对应积分',
  `min_price` decimal(10,2) NOT NULL COMMENT '最小价格',
  `dispatch_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '运费设置 0读取系统  1包邮',
  `member_level_limit_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '会员等级限制  0不限制  1指定可购买  2指定不可购买',
  `member_level_id` varchar(255) NOT NULL DEFAULT '' COMMENT '会员等级id',
  `member_group_limit_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '会员标签限制  0不限制  1指定可购买  2指定不可购买',
  `member_group_id` varchar(255) NOT NULL DEFAULT '' COMMENT '会员标签id',
  `goods_limit_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品限购类型 0 不限购  1每人限购  2  每人每n天限购',
  `goods_limit_num` int(11) NOT NULL DEFAULT '0' COMMENT '每人限购',
  `goods_limit_day` int(11) NOT NULL DEFAULT '0' COMMENT '每天限购',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态  0下架  1上架 -1 原商品修改规格导致下架',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `is_delete` tinyint(11) NOT NULL DEFAULT '0' COMMENT '是否已删除',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_list` (`type`,`is_delete`) USING BTREE,
  KEY `idx_goods` (`goods_id`,`type`,`is_delete`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='应用-积分商城-商品';");

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

        echo "m220830_071548_credit_shop_goods cannot be reverted.\n";

        return true;
    }
}
