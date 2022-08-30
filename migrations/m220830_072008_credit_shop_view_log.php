<?php

use yii\db\Migration;

/**
 * Class m220830_072008_credit_shop_view_log
 */
class m220830_072008_credit_shop_view_log extends Migration
{
    /**
     * 获取表前缀
     * @return string
     * @author 青岛开店星信息技术有限公司
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'credit_shop_view_log';
    }

    /**
     * 更新
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function safeUp(): bool
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '积分商品id',
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_shop` (`created_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='应用-积分商城-浏览记录';");

        return true;
    }

    /**
     * 回滚
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public function safeDown(): bool
    {
        // 删除表
        $this->dropTable($this->getTableName());

        echo "m220830_072008_credit_shop_view_log cannot be reverted.\n";

        return true;
    }
}
