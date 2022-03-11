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

namespace shopstar\models\virtualAccount;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%app_virtual_account_order_map}}".
 *
 * @property int $id
 * @property int $order_id 订单id
 * @property int $virtual_account_id 卡密库id
 * @property int $virtual_account_data_id 卡密数据
 * @property int $use_description 使用说明
 * @property string $use_description_title 使用说明-文字标题
 * @property string $use_description_remark 使用说明-备注
 * @property int $use_address 使用地址 1开启
 * @property string $use_address_title 使用地址-文字标题
 * @property string $data 数据
 * @property string $config key值字段
 * @property string $use_address_address 使用说明-链接地址
 * @property string $to_mailer 接收邮箱
 * @property int $is_deleted 是否删除 1删除
 */
class VirtualAccountOrderMapModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_virtual_account_order_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'virtual_account_id', 'virtual_account_data_id', 'use_description', 'use_address', 'is_deleted'], 'integer'],
            [['data', 'config'], 'string'],
            [['use_description_title', 'use_address_title'], 'string', 'max' => 100],
            [['use_description_remark', 'use_address_address'], 'string', 'max' => 250],
            [['to_mailer'], 'string', 'max' => 255],
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
            'virtual_account_id' => '卡密库id',
            'virtual_account_data_id' => '卡密数据',
            'use_description' => '使用说明',
            'use_description_title' => '使用说明-文字标题',
            'use_description_remark' => '使用说明-备注',
            'use_address' => '使用地址 1开启',
            'use_address_title' => '使用地址-文字标题',
            'data' => '数据',
            'config' => 'key值字段',
            'use_address_address' => '使用说明-链接地址',
            'to_mailer' => '接收邮箱',
            'is_deleted' => '是否删除 1删除',
        ];
    }

    /**
     * 查询单条数据
     * @param $orderId
     * @return VirtualAccountOrderMapModel|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getInfo($orderId)
    {
        return self::findOne(['order_id' => $orderId]);
    }

    /**
     * 查询订单关联的卡密详情信息
     * @param $orderId
     * @return array|null
     * @author 青岛开店星信息技术有限公司
     */
    public static function getDetails($orderId)
    {
        $orderVirtualAccountDataMap = self::find()
            ->where([
                'order_id' => $orderId
            ]);

        $orderVirtualAccountDataMap->select([
            'to_mailer',
            'data',
            'use_description',
            'use_description_title',
            'use_description_remark',
            'use_address',
            'use_address_title',
            'use_address_address',
            'config',
        ]);
        $result = $orderVirtualAccountDataMap->asArray()->get();
        return $result ?? [];
    }

    /**
     * 删除订单下的关联卡密库map数据
     * @return void
     * @author 青岛开店星信息技术有限公司
     */
    public static function deleteOrderVirtualAccountDataMap($orderId)
    {
        $model = self::find()->where(['order_id' => $orderId])->select(['id'])->asArray()->get();
        if ($model) {
            foreach ($model as $value) {
                self::updateAll(['is_deleted' => 1], ['id' => $value['id']]);
            }
        }
    }

    /**
     * 查询列表
     * @param $orderId
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getMapList($orderId)
    {
        $result = self::find()->where(['order_id' => $orderId])->select(['virtual_account_data_id'])->asArray()->all();
        if ($result) {
            return array_column($result, 'virtual_account_data_id');
        }
    }
}