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
use shopstar\helpers\DateTimeHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%app_express_helper_success_record}}".
 *
 * @property int $id
 * @property int $order_id 订单id
 * @property int $order_goods_id 订单商品id
 * @property string $created_at 创建时间
 * @property string $express_template_info 电子面单模板info
 */
class ExpressHelperSuccessRecordModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_express_helper_success_record}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'order_goods_id'], 'integer'],
            [['created_at'], 'safe'],
            [['express_template_info'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => '订单id',
            'order_goods_id' => '订单商品id',
            'created_at' => '创建时间',
            'express_template_info' => '电子面单模板info',
        ];
    }


    /**
     * 批量入库
     * @param array $data
     * @param int $expressTemplateId
     * @return bool|int
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public static function insertData(array $data, int $expressTemplateId = 0)
    {
        //入库数据初始化
        $insertData = [];

        //入库时间
        $insertTime = DateTimeHelper::now();

        // 查询模板数据
        if ($expressTemplateId) {
            $expressTemplateInfo = ExpressHelperExpressTemplateModel::find()->where(['id' => $expressTemplateId])->first();
        }

        //组装入库数据
        foreach ($data as $dataIndex => $dataItem) {
            $orderId = $dataItem['order_id'];
            foreach ((array)$dataItem['order_goods_id'] as $orderGoodsIndex => $orderGoodsItem) {
                $insertData[] = [
                    'order_id' => $orderId,
                    'order_goods_id' => $orderGoodsItem,
                    'created_at' => $insertTime,
                    'express_template_info' => Json::encode($expressTemplateInfo),
                ];
            }
        }

        if (empty($insertData)) {
            return false;
        }

        //入库
        return self::batchInsert(array_keys($insertData[0]), $insertData);
    }

    /**
     * 获取订单商品打印次数
     * @param $orderGoodsId
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getPrintNum($orderGoodsId)
    {
        return self::find()
            ->where([
                'order_goods_id' => $orderGoodsId,
            ])
            ->groupBy(['order_goods_id'])
            ->indexBy('order_goods_id')
            ->asArray()
            ->select([
                'count(order_goods_id) as print_num',
                'order_goods_id',
                'order_id'
            ])
            ->all();
    }

}