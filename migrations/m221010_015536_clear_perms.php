<?php

use yii\db\Migration;

/**
 * Class m221010_015536_clear_perms
 */
class m221010_015536_clear_perms extends Migration
{
    /**
     * {@inheritdoc}
     * @author 青岛开店星信息技术有限公司
     */
    public function up()
    {
        // 删除权限缓存
        \shopstar\config\modules\permission\ShopPermissionConfig::deleteConfigCache();
    }
}
