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


/**
 * This is the model class for table "{{%goods_option}}".
 *
 * @property int $id
 * @property int $goods_id 商品ID
 * @property string $title 规格标题
 * @property string $thumb 缩略图
 * @property string $price 现价
 * @property string $cost_price 成本价
 * @property string $original_price 原价
 * @property int $stock 库存
 * @property int $stock_warning 库存预警
 * @property int $sales 销量
 * @property string $weight 重量
 * @property string $goods_sku 商品编码
 * @property string $bar_code 商品条码
 * @property string $specs 规格项组合
 * @property int $sort_by 排序
 * @property int $virtual_account_id 虚拟卡密ID
 */
class GoodsOptionModel extends BaseActiveRecord
{
    use SpecTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_option}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'stock', 'stock_warning', 'sales', 'sort_by', 'virtual_account_id'], 'integer'],
            [['price', 'cost_price', 'original_price', 'weight'], 'number'],
            [['specs'], 'string'],
            [['title', 'thumb', 'goods_sku', 'bar_code'], 'string', 'max' => 191],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => '商品ID',
            'title' => '规格标题',
            'thumb' => '缩略图',
            'price' => '现价',
            'cost_price' => '成本价',
            'original_price' => '原价',
            'stock' => '库存',
            'stock_warning' => '库存预警',
            'sales' => '销量',
            'weight' => '重量',
            'goods_sku' => '商品编码',
            'bar_code' => '商品条码',
            'specs' => '规格项组合',
            'sort_by' => '排序',
            'virtual_account_id' => '虚拟卡密ID',
        ];
    }

    /**
     * @param int $goodsId
     * @return array|\yii\db\ActiveRecord[]
     * @author 青岛开店星信息技术有限公司
     */
    public static function getListByGoodsId(int $goodsId)
    {
        return self::find()->where(['goods_id' => $goodsId])->asArray()->all();
    }

    /**
     * 查询卡密库关联
     * @param $virtualAccountId
     * @author 青岛开店星信息技术有限公司
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getListByVirtualAccountId($virtualAccountId)
    {
        return self::find()->where([
            'virtual_account_id'=>$virtualAccountId])
            ->select(['goods_id','stock'])
            ->asArray()
            ->all();
    }

    /**
     * 减少库存
     * @param $virtualAccountId
     * @author 青岛开店星信息技术有限公司
     * @return void
     */
    public static function updateReduceCount($virtualAccountId,$count)
    {
        self::updateAllCounters(['stock' => -$count], ['virtual_account_id' => $virtualAccountId]);
    }

    /**
     * 根据多规格的id查询相应的虚拟卡密库id
     * @param $id
     * @author 青岛开店星信息技术有限公司
     * @return array|null
     */
    public static function getInfoById($id)
    {
        return self::find()->where(['id' => $id])->select('virtual_account_id')->first()['virtual_account_id'] ?? '';
    }
}