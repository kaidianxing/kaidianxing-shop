<?php

use yii\db\Migration;

/**
 * PC商城商品组表结构
 * Class m220804_032547_pc_goods_group
 * @package ${NAMESPACE}
 * @author likexin
 */
class m220804_032547_pc_goods_group extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'pc_goods_group';
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
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0关闭，1开启',
  `name` varchar(50) NOT NULL DEFAULT '',
  `main_img` varchar(150) NOT NULL DEFAULT '',
  `main_img_url` varchar(150) NOT NULL DEFAULT '',
  `goods_type` tinyint(1) NOT NULL COMMENT '1代表手动选择，2代表选择分类，3代表手动分组',
  `goods_info` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `top_advertise_img` varchar(150) NOT NULL DEFAULT '',
  `top_advertise_img_url` varchar(150) NOT NULL DEFAULT '',
  `bottom_advertise_img` varchar(150) NOT NULL DEFAULT '',
  `bottom_advertise_img_url` varchar(150) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='PC商城-商品组';");

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

        echo "m220804_032547_pc_goods_group cannot be reverted.\n";

        return true;
    }

}
