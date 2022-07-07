<?php

use yii\db\Migration;

/**
 * 清除权限缓存
 * Class m220707_032944_clear_perms
 */
class m220707_032944_clear_perms extends Migration
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

}
