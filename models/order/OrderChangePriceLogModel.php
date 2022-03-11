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
 * This is the model class for table "{{%order_change_price_log}}".
 *
 * @property int $id
 * @property int $uid 用户id
 * @property int $order_id 订单id
 * @property string $change_price 改价变动金额，有符号
 * @property string $before_price 改价前金额
 * @property string $after_price 改价后金额
 * @property string $created_at 操作时间
 * @property string $ext_info 扩展信息
 */
class OrderChangePriceLogModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_change_price_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uid', 'order_id'], 'integer'],
            [['change_price', 'before_price', 'after_price'], 'number'],
            [['created_at'], 'safe'],
            [['ext_info'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => '用户id',
            'order_id' => '订单id',
            'change_price' => '改价变动金额，有符号',
            'before_price' => '改价前金额',
            'after_price' => '改价后金额',
            'created_at' => '操作时间',
            'ext_info' => '扩展信息',
        ];
    }

    /**
     * 获取订单改价次数
     * @param $orderId
     * @return int
     * @author 青岛开店星信息技术有限公司
     */
    public static function getChangePriceCount($orderId)
    {
        $count = self::find()->where(['order_id' => $orderId])->count();

        return (int)$count;
    }
}