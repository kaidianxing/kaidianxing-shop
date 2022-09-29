<?php

use yii\db\Migration;

/**
 * Class m220926_063038_credit_sign_member_total
 */
class m220926_063038_credit_sign_member_total extends Migration
{
    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'credit_sign_member_total';
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员ID',
  `first_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '首次签到时间',
  `last_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '上次签到时间',
  `sign_day` int(10) NOT NULL DEFAULT '0' COMMENT '总签到天数',
  `continuity_day` int(10) NOT NULL DEFAULT '0' COMMENT '连续签到天数',
  `longest_day` int(10) NOT NULL DEFAULT '0' COMMENT '最长连续签到天数',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `is_remind` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启签到提醒',
  PRIMARY KEY (`id`),
  KEY `idx_member_id` (`member_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员签到记录统计表';");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // 删除表
        $this->dropTable($this->getTableName());
        echo "m220926_063038_credit_sign_member_total cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220926_063038_credit_sign_member_total cannot be reverted.\n";

        return false;
    }
    */
}
