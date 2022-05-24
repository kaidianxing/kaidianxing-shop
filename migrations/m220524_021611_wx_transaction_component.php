<?php

use yii\db\Migration;

/**
 * Class m220524_021611_wx_transaction_component
 */
class m220524_021611_wx_transaction_component extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'wx_transaction_component';
    }

    /**
     * 更新
     * @author likexin
     */
    public function safeUp(): bool
    {
        $this->execute("CREATE TABLE `{$this->getTableName()}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL DEFAULT '0' COMMENT '店铺id',
  `sub_shop_id` int(11) NOT NULL DEFAULT '0' COMMENT '子店铺id',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  `category_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类id',
  `category_name` varchar(100) NOT NULL DEFAULT '' COMMENT '分类名称',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态 10审核撤销 20审核中 30 审核成功 40审核失败',
  `remote_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '中台商品状态 1下架状态 2上架状态',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_shop_id_sub_shop_id_goods_id` (`shop_id`,`sub_shop_id`,`goods_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='应用-微信自定义交易组件-商品列表';");

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

        echo "m220524_021611_wx_transaction_component cannot be reverted.\n";

        return true;
    }

}
