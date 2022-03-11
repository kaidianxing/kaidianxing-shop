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

namespace shopstar\models\order;

use shopstar\bases\model\BaseActiveRecord;



/**
 * This is the model class for table "{{%dispatch_notify_log}}".
 *
 * @property int $id auto increment
 * @property int $type 配送方式1达达2码科
 * @property int $order_id 订单ID
 * @property string $notify 返回值
 * @property int $status 订单状态
 * @property string $created_at 创建时间
 */
class DispatchNotifyLogModel extends BaseActiveRecord
{

    // 待接单 status = 1
    // 待取货 status = 2
    // 已到店 status = 100
    // 配送中 status = 3
    // 已完成 status = 4
    // 已取消 status = 5

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%dispatch_notify_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'order_id', 'status'], 'integer'],
            [['created_at'], 'safe'],
            [['notify'], 'string', 'max' => 1024],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'auto increment',
            'type' => '配送方式1达达2码科',
            'order_id' => '订单ID',
            'notify' => '返回值',
            'status' => '订单状态',
            'created_at' => '创建时间',
        ];
    }

}