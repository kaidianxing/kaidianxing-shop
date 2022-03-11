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

use shopstar\bases\model\BaseActiveRecord;


/**
 * This is the model class for table "{{%goods_virtual_account_map}}".
 *
 * @property int $id
 * @property int $goods_id 订单id
 * @property int $virtual_account_id 卡密数据
 * @property int $is_deleted 是否删除 1删除
 */
class GoodsVirtualAccountMapModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_virtual_account_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'virtual_account_id', 'is_deleted'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => '订单id',
            'virtual_account_id' => '卡密数据',
            'is_deleted' => '是否删除 1删除',
        ];
    }

    /**
     * 获取商品关联虚拟卡密库id
     * @param int $goodsId
     * @return array|string
     * @author 青岛开店星信息技术有限公司
     */
    public static function getVirtualAccountId(int $goodsId)
    {
        $params = [
            'goods_id' => $goodsId,
        ];

        $result = self::find()->where($params)->select(['virtual_account_id id'])->first();
        return $result ? $result['id'] : '';
    }

}
