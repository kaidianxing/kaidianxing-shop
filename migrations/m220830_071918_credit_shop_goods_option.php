<?php

use yii\db\Migration;

/**
 * Class m220830_071918_credit_shop_goods_option
 */
class m220830_071918_credit_shop_goods_option extends Migration
{
    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'credit_shop_goods_option';
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
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `credit_shop_goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '积分商品id',
  `option_id` int(11) NOT NULL DEFAULT '0' COMMENT '规格id',
  `credit_shop_credit` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `credit_shop_price` decimal(10,2) NOT NULL COMMENT '价格',
  `credit_shop_stock` int(11) NOT NULL DEFAULT '0' COMMENT '库存',
  `original_stock` int(11) NOT NULL DEFAULT '0' COMMENT '原始库存',
  `sale` int(11) NOT NULL DEFAULT '0' COMMENT '销量',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `is_join` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否参与',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_goods` (`credit_shop_goods_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='应用-积分商城-商品规格';");

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

        echo "m220830_071918_credit_shop_goods_option cannot be reverted.\n";

        return true;
    }
}
