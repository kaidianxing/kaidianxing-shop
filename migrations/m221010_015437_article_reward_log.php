<?php

use yii\db\Migration;

/**
 * Class m221010_015437_article_reward_log
 */
class m221010_015437_article_reward_log extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'article_reward_log';
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章id',
  `to_member_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发放给用户id',
  `from_member_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源自用户id',
  `reward_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '类型 1: 积分 2: 余额',
  `number` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '发放数量',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_article_id` (`article_id`) USING BTREE,
  KEY `idx_to_member_id` (`to_member_id`) USING BTREE,
  KEY `idx_from_member_id` (`from_member_id`) USING BTREE,
  KEY `idx_reward_type` (`reward_type`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='应用-文章营销-奖励发放记录';");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // 删除表
        $this->dropTable($this->getTableName());
        echo "m221010_015437_article_reward_log cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221010_015437_article_reward_log cannot be reverted.\n";

        return false;
    }
    */
}
