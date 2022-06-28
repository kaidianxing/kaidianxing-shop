<?php
/**
 * 开店星新零售管理系统
 * @description 基于Yii2+Vue2.0+uniapp研发，H5+小程序+公众号全渠道覆盖，功能完善开箱即用，框架成熟易扩展二开
 * @author 青岛开店星信息技术有限公司
 * @link https://www.kaidianxing.com
 * @copyright Copyright (c) 2020-2022 Qingdao ShopStar Information Technology Co., Ltd.
 * @copyright 版权归青岛开店星信息技术有限公司所有
 * @warning Unauthorized deletion of copyright information is prohibited.
 * @warning 未经许可禁止私自删除版权信息
 */

namespace shopstar\services\material;

use shopstar\models\material\MaterialModel;
use shopstar\models\shop\ShopSettings;

class IndexService
{
    /**
     * 查询商品素材 判断是否开启一键发圈
     * @param int $goodsId
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function showMaterial(int $goodsId): ?array
    {
        // 查询是否开启
        $setting = ShopSettings::get('material');

        if ($setting['status']) {
            // 直接查询素材是否存在
            $material = MaterialModel::find()->where(['goods_id' => $goodsId, 'is_deleted' => 0])->first();

            if (!$material) {
                $material = [
                    'default_config' => 1,
                    'description_type' => 0,
                    'material_type' => 0,
                    'description' => '📢 现在下单超划算，赶紧下单',
                ];
            } else {
                $material['default_config'] = 0;
            }
        } else {
            $material = [];
        }

        return $material;
    }
}
