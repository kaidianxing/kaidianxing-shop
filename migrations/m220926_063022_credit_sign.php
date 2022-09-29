<?php

use yii\db\Migration;

/**
 * Class m220926_063022_credit_sign
 */
class m220926_063022_credit_sign extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'credit_sign';
    }


    /**
     * 追加默认数据
     * @return string
     * @author yuning
     */
    private function getNoticeTable(): string
    {
        return $this->db->tablePrefix . 'notice_wechat_default_template';
    }

    /**
     * 追加默认数据
     * @return string
     * @author yuning
     */
    private function getWxAppNoticeTable(): string
    {
        return $this->db->tablePrefix . 'notice_wxapp_default_template';
    }


    /**
     * 更新
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function safeUp(): bool
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `job_id` int(11) NOT NULL DEFAULT '0' COMMENT '队列任务ID',
  `activity_name` varchar(25) NOT NULL DEFAULT '' COMMENT '活动名称',
  `client_type` varchar(50) NOT NULL DEFAULT '' COMMENT '客户端类型',
  `ext_field` text NOT NULL COMMENT '活动规则等备用字段',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '活动状态：0未开始；1进行中；-1停止；-2手动停止；',
  `start_time` date NOT NULL DEFAULT '0000-00-00' COMMENT '活动开始时间',
  `end_time` date NOT NULL DEFAULT '0000-00-00' COMMENT '活动结束时间',
  `stop_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '停止时间',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '新增时间',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更改时间',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未删除 1已删除',
  PRIMARY KEY (`id`),
  KEY `idx_job_id` (`job_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用-积分签到-活动详情';");


        // 微信模版默认消息
        $this->execute("INSERT INTO `{$this->getNoticeTable()}` ( `name`, `scene_code`, `template_code`, `template_name`, `content`, `group_id`) VALUES ( '积分签到每日提醒', 'credit_sign_notice', 'OPENTM416070406', '积分签到每日提醒', '[{\"key\":\"keyword1\",\"value\":\"[业务名称]\"},{\"key\":\"keyword2\",\"value\":\"[当前进度]\"},{\"key\":\"keyword3\",\"value\":\"[执行人]\"},{\"key\":\"keyword4\",\"value\":\"[执行时间]\"},{\"key\":\"remark\",\"value\":\"[备注]\"}]', 0)");

        // 小程序默认消息
        $this->execute("INSERT INTO `{$this->getWxAppNoticeTable()}` ( `name`, `scene_code`, `template_id`, `template_name`, `kid_list`, `scene_desc`, `content`) VALUES ( '积分签到每日提醒', 'credit_sign_notice', '6240', '积分签到每日提醒通知', '[5,6,11,3]', '积分签到每日提醒通知', '[{\"key\":\"thing5\",\"value\":\"[活动名称]\"},{\"key\":\"thing6\",\"value\":\"[签到奖励]\"},{\"key\":\"number11\",\"value\":\"[累计签到]\"},{\"key\":\"thing3\",\"value\":\"[温馨提示]\"}]')");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // 删除表
        $this->dropTable($this->getTableName());

        echo "m220926_063022_credit_sign cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220926_063022_credit_sign cannot be reverted.\n";

        return false;
    }
    */
}
