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

namespace shopstar\models\goods;

use yii\base\InvalidArgumentException;

trait SpecTrait
{
    /**
     * 替换规格商品
     * @param array $datas
     * @return array|int|void
     * @author 青岛开店星信息技术有限公司
     */
    public static function replace($datas = [])
    {
        if (empty($datas)) {
            throw new InvalidArgumentException('数据为空');
        }

        if (empty($datas['goods_id'])) {
            throw new InvalidArgumentException('商品ID为空');
        }

        if (!empty($datas['id'])) {
            $model = self::findOne($datas['id']);
        }
        if (empty($model)) {
            $model = new self();
        }

        $model->setAttributes($datas);

        if (!$model->save()) {
            throw new InvalidArgumentException($model->getErrorMessage());
        }

        return $model->id;
    }
}