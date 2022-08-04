<?php

use yii\db\Migration;

/**
 * Class m220804_062306_clear_perms
 */
class m220804_062306_clear_perms extends Migration
{
    /**
     * {@inheritdoc}
     * @author likexin
     */
    public function up()
    {
        // 删除权限缓存
        \shopstar\config\modules\permission\ShopPermissionConfig::deleteConfigCache();
    }

    public function down()
    {
        echo "m220804_062306_clear_perms cannot be reverted.\n";

        return false;
    }
}
