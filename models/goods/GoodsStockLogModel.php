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

use shopstar\helpers\DateTimeHelper;

/**
 * This is the model class for table "{{%goods_cart}}".
 *
 * @property int $id Id
 * @property int $order_id 订单id
 * @property int $goods_id 商品id
 * @property int $method 变动库存方式 1加0减
 * @property int $stock 变动数量
 * @property int $sales 变动销量
 * @property int $created_at 变动时间
 * @property int $reason 变动原因
 */
class GoodsStockLogModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_stock_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'method', 'stock', 'sales', 'goods_id'], 'integer'],
            [['created_at'], 'safe'],
            [['reason'], 'string', 'max' => 191],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'order_id' => '订单id',
            'goods_id' => '商品id',
            'method' => '变动方式 1加0减',
            'stock' => '变动数量',
            'sales' => '变动销量',
            'created_at' => '变动时间',
            'reason' => '变动原因',
        ];
    }

    /**
     * 保存log
     * @param array $data
     * @return bool
     * @author 青岛开店星信息技术有限公司
     */
    public static function saveData(array $data)
    {
        $model = new self();
        $data['created_at'] = DateTimeHelper::now();
        $model->setAttributes($data);
        return $model->save();
    }
}
