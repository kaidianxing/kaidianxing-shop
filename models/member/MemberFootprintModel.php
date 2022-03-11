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

namespace shopstar\models\member;

use shopstar\bases\model\BaseActiveRecord;

use shopstar\helpers\DateTimeHelper;

/**
 * This is the model class for table "{{%footprint}}".
 *
 * @property int $id 主键
 * @property int|null $goods_id 商品id
 * @property int|null $member_id 商品id
 * @property string|null $created_at 创建时间
 */
class MemberFootprintModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%footprint}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'member_id'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'goods_id' => '商品id',
            'member_id' => '会员id',
            'created_at' => '创建时间',
        ];
    }

    public static function create($member_id)
    {
        self::deleteAll(compact('member_id'));
        $self = new self();
        $self->setAttributes(compact('member_id'));
        $self->created_at = DateTimeHelper::now();
        $self->save();

        return true;
    }
}