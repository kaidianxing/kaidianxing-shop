<?php

use yii\db\Migration;

/**
 * Class m220524_021526_wx_audit_category_images
 */
class m220524_021526_wx_audit_category_images extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'wx_audit_category_images';
    }

    /**
     * 更新
     * @author likexin
     */
    public function safeUp(): bool
    {
        $this->execute("CREATE TABLE `{$this->getTableName()}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wx_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联自定义交易表id',
  `audit_category_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联的分类审核表id',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态 10营业执照 20资质材料',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `category_id` (`audit_category_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='应用-微信自定义交易组件-类目资质审核关联图片';");

        return true;
    }

    /**
     * 回滚
     * @author likexin
     */
    public function safeDown(): bool
    {
        // 删除表
        $this->dropTable($this->getTableName());

        echo "m220524_021526_wx_audit_category_images cannot be reverted.\n";

        return true;
    }

}
