<?php

use yii\db\Migration;

/**
 * 拼团表结构
 * Class m220707_032913_groups_crew
 * @package migrations
 * @author likexin
 */
class m220707_032913_groups_crew extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'groups_crew';
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
  `team_id` int(11) NOT NULL DEFAULT '0' COMMENT '团id',
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员id',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单id',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动商品ID',
  `is_leader` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是团长 1是 0否',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '参团时间',
  `is_valid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否有效1是0否',
  PRIMARY KEY (`id`),
  KEY `idx_team_id` (`team_id`) USING BTREE,
  KEY `idx_member_id` (`member_id`) USING BTREE,
  KEY `idx_is_vaild` (`is_valid`) USING BTREE,
  KEY `idx_team_id_member_id` (`team_id`,`member_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='拼团-团员';");

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

        echo "m220707_032913_groups_crew cannot be reverted.\n";

        return true;
    }

}
