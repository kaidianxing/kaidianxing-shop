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
use shopstar\constants\expressHelper\ExpressHelperLogConstant;

/**
 * This is the model class for table "{{%send_bill_log}}".
 *
 * @property int $id
 * @property int $order_id
 * @property int $status 状态 10打印失败 20打印成功
 */
class ExpressHelperSendBillLogModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%express_helper_send_bill_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id', 'status'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'status' => '状态',
        ];
    }

    /**
     * 添加log动作
     * @param int $orderId
     * @return array|ExpressHelperSendBillLogModel
     * @author 青岛开店星信息技术有限公司
     */
    public static function addLog(int $orderId)
    {
        $log = new self();

        $attribute = [
            'order_id' => $orderId,
            'status' => ExpressHelperLogConstant::EXPRESS_HELPER_STATUS_START,
        ];

        $log->setAttributes($attribute);

        if (!$log->save()) {
            return error($log->getErrorMessage());
        }

        return $log;
    }


    /**
     * 获取今日单号排序
     * @author 青岛开店星信息技术有限公司
     */
    public static function getNowNumber()
    {

    }

    /**
     * 获取发货单打印次数
     * @param $orderGoodsId
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getCount($orderGoodsId)
    {
        return self::find()
            ->where([
                'order_id' => $orderGoodsId,
                'status' => ExpressHelperLogConstant::EXPRESS_HELPER_STATUS_SUCCESS,
            ])
            ->select([
                'count(id) bill_print_num',
                'order_id',
            ])
            ->indexBy('order_id')
            ->groupBy(['order_id'])
            ->asArray()
            ->all();
    }

}