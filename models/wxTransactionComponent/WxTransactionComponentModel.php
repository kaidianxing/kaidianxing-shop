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

namespace shopstar\models\wxTransactionComponent;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%wx_transaction_component}}".
 *
 * @property int $id
 * @property int $goods_id 商品id
 * @property int $category_id 分类id
 * @property string $category_name 分类名称
 * @property int $status 状态 10审核撤销 20审核中 30 审核成功 40审核失败
 * @property int $remote_status 中台商品状态 1下架状态 2上架状态
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class WxTransactionComponentModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%wx_transaction_component}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['goods_id', 'category_id', 'status', 'remote_status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['category_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'goods_id' => '商品id',
            'category_id' => '分类id',
            'category_name' => '分类名称',
            'status' => '状态 10审核撤销 20审核中 30 审核成功 40审核失败',
            'remote_status' => '中台商品状态 1下架状态 2上架状态',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
        ];
    }
}
