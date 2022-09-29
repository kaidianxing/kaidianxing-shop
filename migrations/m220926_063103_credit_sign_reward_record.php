<?php

use yii\db\Migration;

/**
 * Class m220926_063103_credit_sign_reward_record
 */
class m220926_063103_credit_sign_reward_record extends Migration
{
    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'credit_sign_reward_record';
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `activity_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动ID',
  `sign_id` int(11) NOT NULL DEFAULT '0' COMMENT '签到记录ID',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '奖励类型 0日常 1连续 2递增',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '领取状态 0未领取 1已领取',
  `credit_num` int(10) NOT NULL DEFAULT '0' COMMENT '奖励积分',
  `coupon_num` int(10) NOT NULL DEFAULT '0' COMMENT '优惠券领取数量',
  `content` text NOT NULL COMMENT '奖励内容',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '领取时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '重置状态 0未重置 1已重置',
  `continuity_day` int(10) NOT NULL DEFAULT '0' COMMENT '连续签到天数(冗余字段)',
  PRIMARY KEY (`id`),
  KEY `idx_member_id` (`member_id`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE,
  KEY `idx_sign_id` (`sign_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='奖励记录表';");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // 删除表
        $this->dropTable($this->getTableName());
        echo "m220926_063103_credit_sign_reward_record cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220926_063103_credit_sign_reward_record cannot be reverted.\n";

        return false;
    }
    */
}
