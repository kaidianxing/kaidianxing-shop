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

namespace shopstar\models\printer;


use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%printer_log}}".
 *
 * @property int $id auto increment
 * @property int $task_id 打印任务ID
 * @property int $order_id 订单ID
 * @property int $scene 打印场景 1下单2付款3确认收货
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class PrinterLogModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%printer_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_id', 'order_id', 'scene'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'auto increment',
            'task_id' => '打印任务ID',
            'order_id' => '订单ID',
            'scene' => '打印场景 1下单2付款3确认收货',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 添加日志
     * @param $orderId
     * @param $taskId
     * @param $scene
     * @return array|PrinterLogModel
     * @author 青岛开店星信息技术有限公司
     */
    public static function addLog($orderId, $taskId, $scene)
    {
        $log = new self();

        $attribute = [
            'task_id' => $taskId,
            'order_id' => $orderId,
            'scene' => $scene
        ];

        $log->setAttributes($attribute);

        if (!$log->save()) {
            return error($log->getErrorMessage());
        }

        return $log;
    }
}