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

namespace shopstar\models\sale;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%coupon_map}}".
 *
 * @property int $coupon_id 优惠券id
 * @property int $goods_cate_id 商品或者分类id
 * @property int $type 1是产品 2分类
 */
class CouponMapModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coupon_id', 'goods_cate_id', 'type'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'coupon_id' => '优惠券id',
            'goods_cate_id' => '商品或者分类id',
            'type' => '1是产品 2分类',
        ];
    }

    /**
     * 保存优惠券更新map
     * @param array $data
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function updateMap(array $data)
    {
        // 编辑优惠券删除原来的关系
        if ($data['is_update']) {
            CouponMapModel::deleteAll(['coupon_id' => $data['coupon_id']]);
        }
        $type = 0; // 限制类型  1 商品  2 商品分类
        if ($data['goods_limit'] == 1 || $data['goods_limit'] == 2) {
            $type = 1;
        } else if ($data['goods_limit'] == 3) {
            $type = 2;
        }
        // 商品id 或 商品分类id
        $ids = $data['goods_ids'];

        // 商品使用相关限制
        $insertGoodsDetail = [];
        $fieldsGoods = ['coupon_id', 'goods_cate_id', 'type'];
        if (!empty($ids) && $type != 0) {
            $ids = explode(',', $ids);
            foreach ($ids as $value) {
                $insertGoodsDetail[] = [
                    $data['coupon_id'], // 优惠券id
                    $value, // 限制 商品或分类id
                    $type, // 限制类型
                ];
            }
        }
        try {
            // 插入
            if (!empty($insertGoodsDetail)) {
                CouponMapModel::batchInsert($fieldsGoods, $insertGoodsDetail);
            }
        } catch (\Throwable $exception) {
            return error_log($exception->getMessage());
        }

        return true;
    }
}