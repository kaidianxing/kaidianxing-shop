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
use shopstar\models\core\CoreExpressModel;

/**
 * This is the model class for table "{{%order_package}}".
 *
 * @property int $id id
 * @property int $order_id 订单id
 * @property string $order_goods_ids 订单商品表id
 * @property string $no_express 是否需要快递
 * @property string $express_com 快递名称
 * @property string $express_sn 快递单号
 * @property int $express_id 快递公司id
 * @property string $send_time 发货时间
 * @property string $remark 发货备注
 * @property int $is_city_distribution 是否是同城配送 0否 1是
 * @property int $city_distribution_type 同城配送方式 0商家配送 1达达配送
 * @property string $finish_time 收货时间
 * @property string $express_name 快递公司名称
 */
class OrderPackageModel extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_package}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_goods_ids', 'send_time'], 'required'],
            [['order_id', 'express_id', 'is_city_distribution', 'city_distribution_type', 'no_express'], 'integer'],
            [['order_goods_ids', 'remark'], 'string'],
            [['send_time', 'finish_time'], 'safe'],
            [['express_com'], 'string', 'max' => 30],
            [['express_sn'], 'string', 'max' => 50],
            [['express_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'order_id' => '订单id',
            'order_goods_ids' => '订单商品表id',
            'no_express' => '是否需要快递',
            'express_com' => '快递名称',
            'express_sn' => '快递单号',
            'express_id' => '快递公司id',
            'send_time' => '发货时间',
            'remark' => '发货备注',
            'is_city_distribution' => '是否是同城配送 0否 1是',
            'city_distribution_type' => '同城配送方式 0商家配送 1达达配送',
            'finish_time' => '收货时间',
            'express_name' => '快递公司名称',
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->remark = (string)$this->remark;
        return parent::beforeSave($insert);
    }

    /**
     * 获取一个包裹的信息
     * @param $where
     * @param array $options
     * @return array|null|\yii\db\ActiveRecord
     * @throws \yii\db\Exception
     */
    public static function getOne($where, $options = [])
    {
        if (empty($where)) {
            return [];
        }

        $options = array_merge([
            'fields' => '*',
            'asArray' => true, //是否转为数组
            'withExpress' => true, //是否查询物流公司信息
        ], $options);

        $package = self::find()->where($where)->select($options['fields'])->asArray($options['asArray'])->one();
        if (empty($package)) {
            return [];
        }

        if ($options['withExpress'] && $options['asArray']) {
            self::setPackage($package);
        }

        return $package;
    }

    /**
     * 根据id获取一个包裹的信息
     * @param $packageId
     * @return array|null|\yii\db\ActiveRecord
     * @throws \yii\db\Exception
     */
    public static function getPackageById($packageId)
    {
        $package = self::find()->where(['id' => $packageId])->asArray()->one();
        if (!empty($package)) {
            self::setPackage($package);
        }
        return $package;
    }

    /**
     * 设置一个包裹的信息
     * @param $package
     * @return mixed
     * @throws \yii\db\Exception
     */
    public static function setPackage(&$package)
    {
        if (!empty($package)) {
            $expressList = CoreExpressModel::getAll();
            if (isset($expressList[$package['express_id']])) {
                $package['express_name'] = $package['express_name'] ?: $expressList[$package['express_id']]['name'];
                $package['express_code'] = $expressList[$package['express_id']]['code'];
                $package['express_encoding'] = $expressList[$package['express_id']]['key'];
            }
        }
        return $package;
    }


}