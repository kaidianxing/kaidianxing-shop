<?php

use yii\db\Migration;

/**
 * 新增字段
 * Class m220707_040809_marketring_statistics
 * @package ${NAMESPACE}
 * @author likexin
 */
class m220707_040809_marketring_statistics extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'marketring_statistics';
    }

    /**
     * 更新
     * @author likexin
     */
    public function safeUp(): bool
    {
        $this->execute("ALTER TABLE `{$this->getTableName()}` ADD COLUMN `goods_view_count` int(11) NOT NULL DEFAULT 0 COMMENT '活动浏览量';");

        return true;
    }

    /**
     * 回滚
     * @author likexin
     */
    public function safeDown(): bool
    {
        // 删除表
        $this->dropColumn($this->getTableName(), 'goods_view_count');

        echo "m220707_040809_marketring_statistics cannot be reverted.\n";

        return true;
    }

}
