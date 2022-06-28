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

namespace shopstar\admin\material;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\material\MaterialLogConstant;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\goods\category\GoodsCategoryModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\log\LogModel;
use shopstar\models\material\MaterialModel;
use yii\db\StaleObjectException;
use yii\web\Response;

class IndexController extends KdxAdminApiController
{
    /**
     * 素材列表
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionList()
    {
        $params = [
            'alias' => 'material',
            'where' => [
                'material.is_deleted' => 0
            ],
            'select' => [
                'material.id',
                'material.goods_id',
                'material.description_type',
                'material.description',
                'material.material_type',
                'material.create_time',
                'goods.thumb as goods_thumb',
                'goods.has_option as goods_has_option',
                'goods.type as goods_type',
                'goods.title as goods_title',
            ],
            'searchs' => [
                ['material.description', 'like', 'description'],
                ['material.create_time', 'between', 'create_time'],
                ['goods.title', 'like', 'title'],
            ],
            'leftJoins' => [
                [GoodsModel::tableName() . ' as goods', 'goods.id = material.goods_id'],
            ],
            'orderBy' => [
                'material.id' => SORT_DESC,
            ],
        ];

        $list = MaterialModel::getColl($params, []);

        //处理商品分类返回
        $goodsCategory = GoodsCategoryMapModel::find()
            ->alias('category_map')
            ->leftJoin(GoodsCategoryModel::tableName() . ' category', 'category.id=category_map.category_id')
            ->where(['category_map.goods_id' => array_column($list['list'] ?: [], 'goods_id')])
            ->select([
                'category_map.goods_id',
                'category_map.category_id',
                'category.name',
            ])
            ->asArray()
            ->all();

        foreach ((array)$goodsCategory as $goodsCategoryIndex => $goodsCategoryItem) {
            foreach ($list['list'] as $listIndex => &$listItem) {
                if ($listItem['goods_id'] == $goodsCategoryItem['goods_id']) {
                    $listItem['goods_category'][] = $goodsCategoryItem;
                }
            }
        }

        return $this->result(['data' => $list]);
    }

    /**
     * 详情
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionInfo()
    {
        $id = RequestHelper::getInt('id');
        if (!$id) {
            return $this->error('参数错误');
        }

        $info = MaterialModel::find()->where(['id' => $id])->first();

        // 根据 goods_id 查询 商品信息
        $goods = GoodsModel::find()->where(['id' => $info['goods_id']])->first();
        $info['goods_thumb'] = $goods['thumb'];
        $info['goods_has_option'] = $goods['has_option'];
        $info['goods_type'] = $goods['type'];
        $info['goods_title'] = $goods['title'];

        $info['goods_category']  = GoodsCategoryMapModel::find()
            ->alias('category_map')
            ->leftJoin(GoodsCategoryModel::tableName() . ' category', 'category.id=category_map.category_id')
            ->where(['category_map.goods_id' => $info['goods_id']])
            ->select([
                'category_map.goods_id',
                'category_map.category_id',
                'category.name',
            ])
            ->asArray()
            ->all();


        return $this->result(['data' => $info]);
    }

    /**
     * 添加
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionAdd()
    {
        $params = RequestHelper::post();

        $result = MaterialModel::easyAdd([
            'attributes' => [
                'create_time' => DateTimeHelper::now(),
                'description' => $params['description_type'] ? $params['description'] : '📢 现在下单超划算，赶紧下单',
            ],
            'beforeSave' => function ($result) {
                $exist = MaterialModel::findOne(['goods_id' => $result->goods_id, 'is_deleted' => 0]);
                if (!empty($exist)) {
                    return error('同一商品不可重复添加素材');
                }
            },
            'afterSave' => function ($result) {
                $goods = GoodsModel::findOne(['id' => $result->goods_id]);

                // 日志
                LogModel::write(
                    $this->userId,
                    MaterialLogConstant::MATERIAL_ADD,
                    MaterialLogConstant::getText(MaterialLogConstant::MATERIAL_ADD),
                    $result->id,
                    [
                        'log_data' => $result->attributes,
                        'log_primary' => [
                            '选择商品' => $goods->title,
                            '推广文案' => ($result->description_type ? '自定义 :': '系统默认 :') . $result->description,
                            '上传素材' => $result->material_type ? '视频' : '图片',
                        ],
                        'dirty_identity_code' => [
                            MaterialLogConstant::MATERIAL_ADD,
                            MaterialLogConstant::MATERIAL_EDIT,
                        ],
                    ]
                );
            }
        ]);

        return $this->result($result);
    }

    /**
     * 编辑
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionEdit()
    {
        $params = RequestHelper::post();

        $result = MaterialModel::easyEdit([
            'beforeSave' => function ($result) use ($params) {
                $exist = MaterialModel::findOne(['goods_id' => $result->goods_id, 'is_deleted' => 0]);
                if (!empty($exist) && $exist->id != $result->id) {
                    return error('同一商品不可重复添加素材');
                }

                $result->description = $params['description_type'] ? $params['description'] : '📢 现在下单超划算，赶紧下单';
            },
            'afterSave' => function ($result) {
                $goods = GoodsModel::findOne(['id' => $result->goods_id]);

                // 日志
                LogModel::write(
                    $this->userId,
                    MaterialLogConstant::MATERIAL_EDIT,
                    MaterialLogConstant::getText(MaterialLogConstant::MATERIAL_EDIT),
                    $result->id,
                    [
                        'log_data' => $result->attributes,
                        'log_primary' => [
                            '选择商品' => $goods->title,
                            '推广文案' => ($result->description_type ? '自定义 :': '系统默认 :') . $result->description,
                            '上传素材' => $result->material_type ? '视频' : '图片',
                        ],
                        'dirty_identity_code' => [
                            MaterialLogConstant::MATERIAL_ADD,
                            MaterialLogConstant::MATERIAL_EDIT,
                        ],
                    ]
                );
            }
        ]);

        return $this->result($result);
    }

    /**
     * 删除
     * @return array|int[]|Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete()
    {
        $res = MaterialModel::easyRecycle([
            'andWhere' => [
                'is_deleted' => 0,
            ],
            'afterSave' => function ($model) {
                $goods = GoodsModel::findOne(['id' => $model->goods_id]);

                // 日志
                LogModel::write(
                    $this->userId,
                    MaterialLogConstant::MATERIAL_DELETE,
                    MaterialLogConstant::getText(MaterialLogConstant::MATERIAL_DELETE),
                    $model->id,
                    [
                        'log_data' => ['id' => $model->id, 'is_deleted' => 1],
                        'log_primary' => [
                            '商品名称' => $goods->title,
                        ],
                    ]
                );
            }
        ]);

        if (is_error($res)) {
            return $this->error($res);
        }

        return $this->success();
    }
}
