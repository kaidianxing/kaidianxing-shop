<?php

use yii\db\Migration;

/**
 * Class m220919_022852_wechat_customer_service_servicer
 */
class m220919_022852_wechat_customer_service_servicer extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'wechat_customer_service_servicer';
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` varchar(100) NOT NULL DEFAULT '' COMMENT '企业ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '客服名称',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '客服链接',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0:未删除, 1:已删除 ',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_company_id` (`company_id`) USING BTREE,
  KEY `idx_is_deleted` (`is_deleted`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='微信客服-客服';");

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // 删除表
        $this->dropTable($this->getTableName());

        echo "m220919_022852_wechat_customer_service_servicer cannot be reverted.\n";

        return false;
    }


}
