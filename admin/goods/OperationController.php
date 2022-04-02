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

namespace shopstar\admin\goods;

use shopstar\bases\KdxAdminApiController;
use shopstar\constants\goods\GoodsStatusConstant;
use shopstar\constants\log\goods\GoodsLogConstant;
use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\goods\category\GoodsCategoryModel;
use shopstar\models\goods\GoodsCartModel;
use shopstar\models\goods\GoodsModel;
use shopstar\models\goods\GoodsOptionModel;
use shopstar\models\goods\spec\GoodsSpecModel;
use shopstar\models\log\LogModel;
use shopstar\services\goods\GoodsService;
use yii\helpers\Json;

/**
 * 商品操作
 * Class OperationController
 * @package shopstar\admin\goods
 */
class OperationController extends KdxAdminApiController
{

    /**
     * 获取规格
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetPriceAndStock()
    {
        $goodsId = RequestHelper::getInt('id');
        if (empty($goodsId)) {
            throw new GoodsException(GoodsException::GOODS_OPERATION_GET_PRICE_AND_STOCK_GOODS_PARAMS_ERROR);
        }

        $goods = GoodsModel::findOne($goodsId);
        if (empty($goods)) {
            throw new GoodsException(GoodsException::GOODS_OPERATION_GET_PRICE_AND_STOCK_GOODS_NOT_FOUND_ERROR);
        }
        if ($goods->has_option == 0) {
            return $this->success(['has_option' => $goods->has_option]);
        }

        $data['spec'] = GoodsSpecModel::getSpaceById($goodsId);
        $data['options'] = GoodsOptionModel::find()
            ->where([
                'goods_id' => $goodsId,
            ])
            ->select([
                'id',
                'title',
                'price',
                'stock',
                'specs'
            ])
            ->asArray()->all();

        return $this->success(['has_option' => 1, 'data' => $data]);
    }

    /**
     * 设置商品价格和规格
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSetPriceAndStock()
    {
        $post = RequestHelper::post();
        if (empty($post['goods_id'])) {
            throw new GoodsException(GoodsException::GOODS_OPERATION_SET_PRICE_AND_STOCK_GOODS_PARAMS_ERROR);
        }

        $tr = \Yii::$app->db->beginTransaction();
        //全部的规格价格
        $price = [];
        $stock = [];

        $logData = [];
        //日志主要数据
        $logPrimary = [];
        try {
            $goods = GoodsModel::findOne($post['goods_id']);
            if (empty($goods)) {
                throw new GoodsException(GoodsException::GOODS_OPERATION_SET_PRICE_AND_STOCK_GOODS_NOT_FOUND_ERROR);
            }

            //是否是单规格
            if ($goods->has_option == 0) {
                $goods->price = $post['price'];
                $goods->min_price = $post['price'];
                $goods->max_price = $post['price'];
                $goods->stock = $post['stock'];

                $logPrimary['goods'] = [
                    'title' => $goods['title'],
                    'price' => $post['price'],
                    'stock' => $post['stock']
                ];

                if (!$goods->save()) {
                    throw new GoodsException(GoodsException::GOODS_OPERATION_SET_PRICE_AND_STOCK_GOODS_SAVE_ERROR);
                }
            } else {
                //多规格循环修改
                foreach ($post['options'] as $optionItem) {
                    if (empty($optionItem['id'])) continue;
                    $option = GoodsOptionModel::findOne($optionItem['id']);
                    if (empty($option)) continue;

                    $price[] = $option->price = $optionItem['price'] ?: 0;
                    $stock[] = $option->stock = $optionItem['stock'] ?: 0;

                    $logData['options'][] = $option->attributes;
                    $logPrimary['options'][] = [
                        'title' => $option->title,
                        'price' => $optionItem['price'],
                        'stock' => $optionItem['stock'],
                    ];

                    if (!$option->save()) {
                        throw new GoodsException(GoodsException::GOODS_OPERATION_SET_PRICE_AND_STOCK_GOODS_SAVE_ERROR);
                    }
                }

                //重置商品最大最小价格
                $goods->min_price = min($price);
                $goods->max_price = max($price);
                $goods->price = min($price);
                $goods->stock = array_sum($stock);
                //日志主要字段
                $logPrimary['goods']['min_price'] = $goods->min_price;
                $logPrimary['goods']['max_price'] = $goods->max_price;
                $logPrimary['goods']['price'] = $goods->price;
                $logPrimary['goods']['stock'] = $goods->stock;

                if (!$goods->save()) {
                    throw new GoodsException(GoodsException::GOODS_OPERATION_SET_PRICE_AND_STOCK_GOODS_SAVE_ERROR);
                }
            }

            $logData['goods'] = $goods->attributes;

            //转移Log字段
            $logPrimary = $goods->getLogAttributeRemark($logPrimary);
            //添加操作日志
            LogModel::write(
                $this->userId,
                GoodsLogConstant::GOODS_OPERATION_SET_PRICE_AND_STOCK,
                GoodsLogConstant::getText(GoodsLogConstant::GOODS_OPERATION_SET_PRICE_AND_STOCK),
                $post['goods_id'],
                [
                    'log_data' => $logData,
                    'log_primary' => $logPrimary,
                ]
            );

            $tr->commit();
        } catch (\Throwable $throwable) {
            $tr->rollBack();
            throw new GoodsException(GoodsException::GOODS_OPERATION_SET_PRICE_AND_STOCK_GOODS_SAVE_ERROR, $throwable->getMessage());
        }

        return $this->success();
    }

    /**
     * 批量设置分类
     * @return \yii\web\Response
     * @throws GoodsException
     * @throws \yii\db\Exception
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSetCategory(): \yii\web\Response
    {
        $post = RequestHelper::post();
        if (empty($post['goods_id']) || !is_numeric($post['method']) || empty($post['category_id'])) {
            throw new GoodsException(GoodsException::GOODS_OPERATION_SET_CATEGORY_PARAMS_ERROR);
        }

        //如果是覆盖原有分类，则删除选中商品之前的所有分类
        if ($post['method'] == 1) {
            GoodsCategoryMapModel::deleteAll([
                'goods_id' => $post['goods_id'],
            ]);
        }

        //重新构建商品分类映射关系
        $categoryIds = GoodsCategoryMapModel::getIdCoverParent((array)$post['category_id']);
        $data = [];
        foreach ((array)$post['goods_id'] as $goodsId) {
            foreach ($categoryIds as $categoryId)
                $data[] = [$goodsId, $categoryId];
        }

        GoodsCategoryMapModel::batchInsert(
            ['goods_id', 'category_id'], $data);


        $goodsData = GoodsCategoryMapModel::find()
            ->alias('goods_category_map_model')
            ->leftJoin(GoodsModel::tableName() . ' as goods', 'goods.id=goods_category_map_model.goods_id')
            ->where([
                'goods_category_map_model.goods_id' => $post['goods_id'],
            ])
            ->select([
                'goods.id as goods_id',
                'goods.title',
                'goods_category_map_model.category_id'
            ])
            ->asArray()
            ->all();

        $category = GoodsCategoryModel::find()
            ->select([
                'id',
                'name'
            ])
            ->indexBy('id')
            ->asArray()
            ->all();


        $data = [];
        foreach ($goodsData as $goodsDataIndex => $goodsDataItem) {
            if (isset($category[$goodsDataItem['category_id']])) {
                if (!isset($data[$goodsDataItem['goods_id']])) {
                    $data[$goodsDataItem['goods_id']] = [
                        'title' => $goodsDataItem['title'],
                        'category' => [
                            $category[$goodsDataItem['category_id']]['name']
                        ]
                    ];
                    continue;
                }

                $data[$goodsDataItem['goods_id']]['category'][] = $category[$goodsDataItem['category_id']]['name'];
            }
        }


        $goodsModel = new GoodsModel();
        foreach ($data as $item) {
            $item['category'] = implode(',', $item['category']);
            //转移Log字段
            $logPrimary = $goodsModel->getLogAttributeRemark(['goods' => $item]);

            //添加操作日志
            LogModel::write(
                $this->userId,
                GoodsLogConstant::GOODS_OPERATION_SET_CATEGORY,
                GoodsLogConstant::getText(GoodsLogConstant::GOODS_OPERATION_SET_CATEGORY),
                $post['goods_id'],
                [
                    'log_primary' => $logPrimary,
                ]
            );
        }

        return $this->success();
    }

    /**
     * 批量下架
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUnshelve(): \yii\web\Response
    {
        $goodsId = RequestHelper::post('id');
        $opType = RequestHelper::postInt('op_type'); //操作类型
        $logConst = GoodsLogConstant::GOODS_OPERATION_UNSHELVE;
        if ($opType == 1) {
            $logConst = GoodsLogConstant::GOODS_OPERATION_BATCH_UNSHELVE;
        }

        $goodsList = GoodsModel::find()->where([
            'id' => $goodsId,
        ])->all();

        foreach ($goodsList as $key => $item) {
            /**
             * @var $item GoodsModel
             */
            $item->status = GoodsStatusConstant::GOODS_STATUS_UNSHELVE;

            $logPrimary = $item->getLogAttributeRemark([
                'goods' => [
                    [
                        'title' => $item['title'],
                        'status' => GoodsStatusConstant::getText(GoodsStatusConstant::GOODS_STATUS_UNSHELVE)
                    ]
                ]
            ]);

            try {
                $result = $item->save();

                //添加操作日志
                $result && LogModel::write(
                    $this->userId,
                    $logConst,
                    GoodsLogConstant::getText($logConst),
                    $goodsId,
                    [
                        'log_data' => $item->attributes,
                        'log_primary' => $logPrimary,
                    ]
                );
            } catch (\Throwable $throwable) {
            }
        }

        // 更新购物车状态
        GoodsCartModel::updateAll(['is_lose_efficacy' => 1, 'is_selected' => 0], ['goods_id' => $goodsId]);

        return $this->success();
    }

    /**
     * 批量上架
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionPutaway(): \yii\web\Response
    {
        $goodsId = RequestHelper::post('id');

        $opType = RequestHelper::postInt('op_type'); //操作类型
        $logConst = GoodsLogConstant::GOODS_OPERATION_PUTAWAY;
        if ($opType == 1) {
            $logConst = GoodsLogConstant::GOODS_OPERATION_BATCH_PUTAWAY;
        }

        $goodsList = GoodsModel::find()->where([
            'id' => $goodsId,
            'status' => 0,
        ])->all();

        foreach ($goodsList as $key => $item) {
            /**
             * @var $item GoodsModel
             */
            $item->status = GoodsStatusConstant::GOODS_STATUS_PUTAWAY;
            $extField = Json::decode($item->ext_field);
            $extField['auto_putaway'] = 0;
            $item->ext_field = Json::encode($extField);

            $logPrimary = $item->getLogAttributeRemark([
                'goods' => [
                    [
                        'title' => $item['title'],
                        'status' => GoodsStatusConstant::getText(GoodsStatusConstant::GOODS_STATUS_PUTAWAY)
                    ]
                ]
            ]);

            try {
                $result = $item->save();
                //添加操作日志
                $result && LogModel::write(
                    $this->userId,
                    $logConst,
                    GoodsLogConstant::getText($logConst),
                    $goodsId,
                    [
                        'log_data' => $item->attributes,
                        'log_primary' => $logPrimary,
                    ]
                );
            } catch (\Throwable $throwable) {
            }
        }

        return $this->success();
    }

    /**
     * 批量删除
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionDelete(): \yii\web\Response
    {
        $goodsId = RequestHelper::post('id');
        if (empty($goodsId)) {
            throw new GoodsException(GoodsException::GOODS_OPERATION_DELETE_PARAMS_ERROR);
        }

        $result = GoodsService::deleteGoods($this->userId, $goodsId, GoodsLogConstant::GOODS_OPERATION_BATCH_DELETE);
        return $this->success($result);
    }

    /**
     * 批量恢复
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionRecover(): \yii\web\Response
    {
        $goodsId = RequestHelper::post('id');

        $opType = RequestHelper::postInt('op_type'); //操作类型
        $logConst = GoodsLogConstant::GOODS_OPERATION_RECOVER;
        if ($opType == 1) {
            $logConst = GoodsLogConstant::GOODS_OPERATION_BATCH_RECOVER;
        }

        $result = GoodsService::recover($this->userId, $goodsId, $logConst);
        return $this->success($result);
    }

    /**
     * 批量永久删除
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionForeverDelete(): \yii\web\Response
    {
        $goodsId = RequestHelper::post('id');
        if (empty($goodsId)) {
            throw new GoodsException(GoodsException::GOODS_OPERATION_DELETE_PARAMS_ERROR);
        }

        $result = GoodsService::foreverRemove($this->userId, $goodsId, GoodsLogConstant::GOODS_OPERATION_BATCH_REAL_DELETE);
        return $this->success($result);
    }

}
