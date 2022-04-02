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

namespace shopstar\models\expressHelper;


use shopstar\bases\model\BaseActiveRecord;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "{{%express_helper_request_record}}".
 *
 * @property int $id
 * @property int $order_id 商城订单id
 * @property string $order_no 商城单号
 * @property string $order_goods_id 订单商品id
 * @property string $order_code 请求订单编号
 * @property string $result_message 返回提示信息
 * @property string $logistic_code 快递单号
 * @property string $express_type 物流方式
 * @property string $request_response 请求返回内容
 * @property string $created_at 创建时间
 */
class ExpressHelperRequestRecordModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%express_helper_request_record}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id'], 'integer'],
            [['request_response'], 'string'],
            [['created_at'], 'safe'],
            [['order_no', 'logistic_code'], 'string', 'max' => 32],
            [['order_goods_id', 'order_code'], 'string', 'max' => 191],
            [['result_message'], 'string', 'max' => 255],
            [['express_type'], 'string', 'max' => 16],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => '商城订单id',
            'order_no' => '商城单号',
            'order_goods_id' => '订单商品id',
            'order_code' => '请求订单编号',
            'result_message' => '返回提示信息',
            'logistic_code' => '快递单号',
            'express_type' => '物流方式',
            'request_response' => '请求返回内容',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 获取打印关系
     * @param $orderId
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPrintRelation($orderId)
    {
        $record = self::find()->where([
            'order_id' => $orderId,
        ])->select([
            'order_id',
            'order_goods_id'
        ])->groupBy('order_goods_id')->asArray()->all();

        if (empty($record)) {
            return [];
        }

        foreach ($record as &$recordItem) {
            $recordItem['order_goods_id'] = StringHelper::explode($recordItem['order_goods_id'], ',');
        }

        return $record;
    }
}