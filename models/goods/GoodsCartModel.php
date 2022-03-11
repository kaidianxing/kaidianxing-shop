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

namespace shopstar\models\goods;

use shopstar\bases\model\BaseActiveRecord;
use shopstar\models\member\MemberLevelModel;
use shopstar\models\member\MemberModel;
use shopstar\models\shop\ShopSettings;
use shopstar\services\goods\GoodsService;
use yii\helpers\Json;
/**
 * This is the model class for table "{{%goods_cart}}".
 *
 * @property int $id 购物车id
 * @property int $member_id 买家id
 * @property int $goods_id 商品id
 * @property int $option_id 商品规格id
 * @property int $total 购买商品数量
 * @property string $price 加入购物车时的价格
 * @property int $is_selected 是否选中
 * @property int $is_reelect 是否需要重选规格
 * @property int $is_lose_efficacy 是否失效
 * @property int $identical_dispatch 相同的物流信息 1相同 2不同
 * @property string $source 来源
 * @property string $created_at 加入购物车时间
 * @property string $updated_at 更新时间
 */
class GoodsCartModel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cart}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'goods_id', 'option_id', 'total', 'is_selected', 'is_lose_efficacy', 'identical_dispatch', 'is_reelect'], 'integer'],
            [['price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['source'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '购物车id',
            'member_id' => '买家id',
            'goods_id' => '商品id',
            'option_id' => '商品规格id',
            'total' => '购买商品数量',
            'price' => '加入购物车时的价格',
            'is_selected' => '是否选中',
            'is_reelect' => '是否需要重选规格',
            'is_lose_efficacy' => '是否失效',
            'identical_dispatch' => '相同的物流信息 1相同 2不同',
            'source' => '来源',
            'created_at' => '加入购物车时间',
            'updated_at' => '更新时间',
        ];
    }

}
