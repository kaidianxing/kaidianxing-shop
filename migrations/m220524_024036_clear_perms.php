<?php

use yii\db\Migration;

/**
 * Class m220524_024036_clear_perms
 */
class m220524_024036_clear_perms extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        \shopstar\config\modules\permission\ShopPermissionConfig::deleteConfigCache();
    }

}
