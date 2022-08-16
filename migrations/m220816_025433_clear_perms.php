<?php

use yii\db\Migration;

/**
 * 清除权限
 * Class m220816_025433_clear_perms
 * @author 青岛开店星信息技术有限公司
 */
class m220816_025433_clear_perms extends Migration
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
