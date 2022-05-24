<?php

use yii\db\Migration;

/**
 * Class m220524_021629_order_refund
 */
class m220524_021629_order_refund extends Migration
{

    /**
     * 获取表前缀
     * @return string
     * @author likexin
     */
    private function getTableName(): string
    {
        return $this->db->tablePrefix . 'order_refund';
    }

    /**
     * 更新
     * @author likexin
     */
    public function safeUp(): bool
    {
        $this->execute("ALTER TABLE `{$this->getTableName()}` ADD `aftersale_id` VARCHAR(30) NOT NULL DEFAULT '';");

        return true;
    }

    /**
     * 回滚
     * @author likexin
     */
    public function safeDown(): bool
    {
        // 删除表
        $this->dropColumn($this->getTableName(), 'aftersale_id');

        echo "m220524_021629_order_refund cannot be reverted.\n";

        return true;
    }

}
