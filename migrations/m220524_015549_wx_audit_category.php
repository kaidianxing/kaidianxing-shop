<?php

use yii\db\Migration;

/**
 * Class m220524_015549_wx_audit_category
 * @author likexin
 */
class m220524_015549_wx_audit_category extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'wx_audit_category';
    }

    /**
     * 更新
     * @author likexin
     */
    public function safeUp(): bool
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL DEFAULT '0' COMMENT '店铺id',
  `sub_shop_id` int(11) NOT NULL DEFAULT '0' COMMENT '子店铺id',
  `audit_id` varchar(255) NOT NULL DEFAULT '' COMMENT '审核单号',
  `category_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类id',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态 0审核中 1审核成功 9审核失败',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_shop_id_sub_shop_id_category_id` (`shop_id`,`sub_shop_id`,`category_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='应用-微信自定义交易组件-类目资质审核';");

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


        echo "m220524_015549_wx_audit_category cannot be reverted.\n";

        return true;
    }

}
