<?php

use yii\db\Migration;

/**
 * 拼团表结构
 * Class m220707_032933_groups_team
 * @package migrations
 * @author likexin
 */
class m220707_032933_groups_team extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'groups_team';
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
  `team_no` varchar(128) NOT NULL DEFAULT '' COMMENT '团编号',
  `activity_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动id',
  `leader_id` int(11) NOT NULL DEFAULT '0' COMMENT '团长会员id',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `is_ladder` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是阶梯团 1是 0否',
  `ladder` tinyint(5) NOT NULL DEFAULT '0' COMMENT '阶梯',
  `limit_time` int(11) NOT NULL DEFAULT '0' COMMENT '限时',
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '结束时间',
  `count` int(11) NOT NULL DEFAULT '1' COMMENT '参团人数',
  `success` int(1) NOT NULL DEFAULT '0' COMMENT '是否成团0未成团1成团2过期',
  `success_num` int(11) NOT NULL DEFAULT '0' COMMENT '成团人数',
  `is_valid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否有效 1是0否',
  `is_fictitious` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否虚拟成团0否1是',
  `success_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '成功时间',
  PRIMARY KEY (`id`),
  KEY `idx_activity_id` (`activity_id`) USING BTREE,
  KEY `idx_leader_id` (`leader_id`) USING BTREE,
  KEY `idx_activity_id_leader_id` (`activity_id`,`leader_id`) USING BTREE,
  KEY `idx_is_valid` (`is_valid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='拼团-团';");

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

        echo "m220707_032933_groups_team cannot be reverted.\n";

        return true;
    }

}
