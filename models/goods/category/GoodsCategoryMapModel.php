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

namespace shopstar\models\goods\category;

use shopstar\bases\model\BaseActiveRecord;

/**
 * This is the model class for table "{{%goods_category_map}}".
 *
 * @property $id
 * @property string $goods_id 商品id
 * @property int $category_id 分类id
 */
class GoodsCategoryMapModel extends BaseActiveRecord
{

    /**
     * 更新字段信息
     * @var array updateField
     */
    protected static $updateField = ['goods_id', 'category_id'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%category_map}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'category_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => '商品id',
            'category_id' => '分类id',
        ];
    }

    /**
     * 根据分类id获取商品id
     * @param array $categoryId
     * @param null $categoryStatus
     * @return array
     * @author 青岛开店星信息技术有限公司
     */
    public static function getGoodsIdByCategoryId(array $categoryId, $categoryStatus = null)
    {
        //全部分类的id  重新查询一遍状态
        $categoryModel = GoodsCategoryModel::find()->where([
            'id' => $categoryId,
        ])->select([
            'id',
        ]);

        if ($categoryStatus !== null) {
            $categoryModel->andWhere(['status' => $categoryStatus]);
        }

        return self::find()
            ->where([
                //获取当前入参有效分类id
                'category_id' => $categoryModel->column()
            ])
            ->groupBy('goods_id')
            ->select(['goods_id'])->asArray()->column();
    }


    /**
     * 获取分类id 包含所有父级id
     * @param array $categoryId
     * @return array
     */
    public static function getIdCoverParent(array $categoryId)
    {
        //所有需要返回的id  父级id
        $id = $parentId = $categoryId;

        //递归获取父级
        while (true) {

            $category = GoodsCategoryModel::find()->where([
                'id' => $parentId
            ])->select([
                'id',
                'parent_id',
                'level'
            ])->asArray()->all();

            //如果为空则跳出
            if (empty($category)) {
                break;
            }

            //合并分类id
            $id = array_merge($id, array_column($category, 'id'));

            //父级id置空
            $parentId = [];
            //获取不是顶级分类的父id
            foreach ($category as $item) {
                if ($item['parent_id'] != 0) {
                    $parentId[] = $item['parent_id'];
                }
            }

            //如果没有父级id 则跳出
            if (empty($parentId)) {
                break;
            }
        }

        return array_unique($id);
    }


}