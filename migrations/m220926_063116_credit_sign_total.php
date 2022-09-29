<?php

use yii\db\Migration;

/**
 * Class m220926_063116_credit_sign_total
 */
class m220926_063116_credit_sign_total extends Migration
{
    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'credit_sign_total';
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员ID',
  `activity_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动ID',
  `sign_days` int(10) NOT NULL DEFAULT '0' COMMENT '签到天数',
  `continuity_days` int(10) NOT NULL DEFAULT '0' COMMENT '连签天数',
  `increasing_days` int(10) NOT NULL DEFAULT '0' COMMENT '递增签到天数',
  `longest_days` int(10) NOT NULL DEFAULT '0' COMMENT '最长连续签到天数',
  `current_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '最近签到时间',
  `last_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '上次签到时间',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `idx_member_id` (`member_id`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员活动统计表';");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // 删除表
        $this->dropTable($this->getTableName());
        echo "m220926_063116_credit_sign_total cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220926_063116_credit_sign_total cannot be reverted.\n";

        return false;
    }
    */
}
