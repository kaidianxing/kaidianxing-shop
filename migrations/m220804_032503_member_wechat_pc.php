<?php

use yii\db\Migration;

/**
 * PC微信授权表结构
 * Class m220804_032503_member_wechat_pc
 * @package ${NAMESPACE}
 * @author likexin
 */
class m220804_032503_member_wechat_pc extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'member_wechat_pc';
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
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员id',
  `openid` varchar(50) NOT NULL DEFAULT '' COMMENT 'openid',
  `unionid` varchar(50) NOT NULL DEFAULT '' COMMENT 'unionid',
  `avatar` varchar(191) NOT NULL DEFAULT '' COMMENT '微信头像',
  `nickname` varchar(191) NOT NULL DEFAULT '' COMMENT '微信昵称',
  `access_token` varchar(255) NOT NULL DEFAULT '',
  `refresh_token` varchar(255) NOT NULL DEFAULT '',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 1是0否',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_member_id` (`member_id`) USING BTREE,
  KEY `idx_openid` (`openid`) USING BTREE,
  KEY `idx_unionid` (`unionid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='会员-微信登录-PC商城';");

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

        echo "m220804_032503_member_wechat_pc cannot be reverted.\n";

        return true;
    }

}
