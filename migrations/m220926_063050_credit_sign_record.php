<?php

use yii\db\Migration;

/**
 * Class m220926_063050_credit_sign_record
 */
class m220926_063050_credit_sign_record extends Migration
{
    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'credit_sign_record';
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `activity_id` int(11) NOT NULL DEFAULT '0' COMMENT '签到活动ID',
  `sign_time` date NOT NULL DEFAULT '0000-00-00' COMMENT '签到时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '签到类型：0正常 1补签',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '记录状态：0未重置 1已重置',
  PRIMARY KEY (`id`),
  KEY `idx_member_id` (`member_id`) USING BTREE,
  KEY `idx_activity_id` (`activity_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='签到记录表';");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // 删除表
        $this->dropTable($this->getTableName());
        echo "m220926_063050_credit_sign_record cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220926_063050_credit_sign_record cannot be reverted.\n";

        return false;
    }
    */
}
