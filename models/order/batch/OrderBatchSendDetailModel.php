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

namespace shopstar\models\order\batch;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%order_batch_send_detail}}".
 *
 * @property int $id
 * @property int $batch_id 批量操作id
 * @property string $order_sn 订单编号
 * @property int $order_id 订单id
 * @property string $express_name 物流公司
 * @property string $express_sn 物流单号
 * @property string $created_at 批量发货时间
 */
class OrderBatchSendDetailModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_batch_send_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['batch_id', 'order_id'], 'integer'],
            [['created_at'], 'safe'],
            [['order_sn', 'express_sn'], 'string', 'max' => 30],
            [['express_name'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'batch_id' => '批量操作id',
            'order_sn' => '订单编号',
            'order_id' => '订单id',
            'express_name' => '物流公司',
            'express_sn' => '物流单号',
            'created_at' => '批量发货时间',
        ];
    }
}