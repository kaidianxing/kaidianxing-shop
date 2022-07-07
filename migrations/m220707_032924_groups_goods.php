<?php

use yii\db\Migration;

/**
 * 拼团表结构
 * Class m220707_032924_groups_goods
 * @package migrations
 * @author likexin
 */
class m220707_032924_groups_goods extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'groups_goods';
    }

    /**
     * 更新
     * {@inheritdoc}
     * @author likexin
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `option_id` int(11) NOT NULL DEFAULT '0' COMMENT '规格id',
  `activity_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动id',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `ladder_price` text NOT NULL COMMENT '阶梯金额',
  `leader_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '团长价',
  `is_ladder` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是阶梯团',
  PRIMARY KEY (`id`),
  KEY `idx_goods_id` (`goods_id`,`option_id`,`activity_id`) USING BTREE,
  KEY `idx_is_ladder` (`is_ladder`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='拼团-商品';");

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

        echo "m220707_032924_groups_goods cannot be reverted.\n";

        return true;
    }

}
