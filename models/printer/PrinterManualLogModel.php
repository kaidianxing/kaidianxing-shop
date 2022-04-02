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
 * This is the model class for table "{{%printer_manual_log}}".
 *
 * @property int $id auto increment
 * @property int $order_id 订单
 * @property int $printer_id 打印机
 * @property int $template_id 模板
 * @property string $created_at 创建时间
 */
class PrinterManualLogModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%printer_manual_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'printer_id', 'template_id'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'auto increment',
            'order_id' => '订单',
            'printer_id' => '打印机',
            'template_id' => '模板',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 添加日志
     * @param $orderId
     * @param $printerId
     * @param $templateId
     * @return array|PrinterManualLogModel
     * @author 青岛开店星信息技术有限公司
     */
    public static function addLog($orderId, $printerId, $templateId)
    {
        $log = new self();

        $attribute = [
            'order_id' => $orderId,
            'printer_id' => $printerId,
            'template_id' => $templateId
        ];

        $log->setAttributes($attribute);

        if (!$log->save()) {
            return error($log->getErrorMessage());
        }

        return $log;
    }
}