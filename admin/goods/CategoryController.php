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
use shopstar\constants\log\goods\GoodsLogConstant;
use shopstar\exceptions\goods\GoodsException;
use shopstar\helpers\DateTimeHelper;
use shopstar\helpers\RequestHelper;
use shopstar\models\goods\category\GoodsCategoryMapModel;
use shopstar\models\goods\category\GoodsCategoryModel;
use shopstar\models\log\LogModel;
use shopstar\models\shop\ShopSettings;
use yii\helpers\Json;

/**
 * 商品分类
 * Class CategoryController
 * @package shopstar\admin\goods
 */
class CategoryController extends KdxAdminApiController
{

    /**
     * @var array
     */
    public $configActions = [
        'postActions' => [
            'create',
            'update',
            'forever-delete'
        ],
        'allowPermActions' => [
            'get-list',
        ],
    ];

    /**
     * 获取商品分类相关参数
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetList(): \yii\web\Response
    {
        $list = GoodsCategoryModel::search('');
        $list['level'] = ShopSettings::get('goods_category')['level'];

        return $this->success($list);
    }

    /**
     * 获取单个商品分类
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetOne(): \yii\web\Response
    {
        $id = RequestHelper::getInt('id');
        if (empty($id)) {
            throw new GoodsException(GoodsException::CATEGORY_GET_ONE_PARAMS_ERROR);
        }

        $category = GoodsCategoryModel::getOne($id);
        if (empty($category)) {
            throw new GoodsException(GoodsException::CATEGORY_GET_ONE_NOT_FOUND_ERROR);
        }

        return $this->success($category);
    }


    /**
     * 编辑商品分类
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionUpdate(): \yii\web\Response
    {
        $post = RequestHelper::post();

        if ($post['id']) {
            $category = GoodsCategoryModel::getOne($post['id']);
            if (empty($category)) {
                throw new GoodsException(GoodsException::CATEGORY_SAVE_NOT_FOUND_ERROR);
            }
        }

        $category = !empty($category) ? $category : new GoodsCategoryModel();

        $transaction = \Yii::$app->getDb()->beginTransaction();
        try {
            unset($post['id']);
            $category->setAttributes($post);
            if (empty($category->created_at)) {
                $category->created_at = DateTimeHelper::now();
            }

            $dirtyData = $category->getDirtyAttributes2();
            if (!$category->save()) {
                throw new GoodsException(GoodsException::CATEGORY_SAVE_ERROR);
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw new GoodsException($exception->getCode());

        }

        return $this->success();
    }

    /**
     * 保存
     * @return \yii\web\Response
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSave(): \yii\web\Response
    {
        $post = RequestHelper::post('data');

        if (!empty($post)) {
            $post = Json::decode($post);
            foreach ($post as $oneIndex => $oneLevel) {
                $childrens = array_filter($oneLevel['children'] ?? []);
                $oneResult = GoodsCategoryModel::saveData($this->userId, $oneLevel, 0, 1);
                if (!$oneResult) {
                    continue;
                }

                //二级分类  只有普通店铺类型才支持
                if (!empty($childrens)) {
                    foreach ($childrens as $twoIndex => $twoLevel) {
                        $childrensShildrens = array_filter($twoLevel['children'] ?? []);
                        $twoResult = GoodsCategoryModel::saveData($this->userId, $twoLevel, $oneResult, 2);
                        if (!$twoResult) {
                            continue;
                        }

                        //三级分类
                        if (!empty($childrensShildrens)) {
                            foreach ($childrensShildrens as $threeIndex => $threeLevel) {
                                $threeResult = GoodsCategoryModel::saveData($this->userId, $threeLevel, $twoResult, 3);
                                if (!$threeResult) {
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->success();
    }

    /**
     * 永久删除商品分类
     * @return \yii\web\Response
     * @throws GoodsException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionForeverDelete(): \yii\web\Response
    {
        $id = RequestHelper::post('id');
        if (empty($id)) {
            throw new GoodsException(GoodsException::CATEGORY_DELETE_PARAMS_ERROR);
        }

        $data = GoodsCategoryModel::findAll(['id' => $id]);
        foreach ($data as $item) {
            $item->delete();
            GoodsCategoryMapModel::deleteAll(['category_id' => $item['id']]);
            $logPrimaryData = $item->getLogAttributeRemark([
                'id' => $item->id,
                'name' => $item->name
            ]);

            //添加操作日志
            LogModel::write(
                $this->userId,
                GoodsLogConstant::GOODS_CATEGORY_DELETE,
                GoodsLogConstant::getText(GoodsLogConstant::GOODS_CATEGORY_DELETE),
                [
                    'log_data' => $item->attributes,
                    'log_primary' => $logPrimaryData,
                ]
            );
        }

        return $this->success();
    }

    /**
     * 分类设置
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSetSetting()
    {
        $post = RequestHelper::postArray('data');
        if (empty($post)) {
            throw new GoodsException(GoodsException::CATEGORY_SET_SETTING_PARAMS_ERROR);
        }

        ShopSettings::set('goods_category', [
            'level' => $post['level'] ?: 0, //等级
            'style' => $post['style'] ?: 0, //样式
            'adv_url' => $post['adv_url'] ?: '', //广告
            'template_type' => $post['template_type'] ?: 0, //模板类型
            'title' => $post['title'] ?: ShopSettings::getDefaultSettings('goods_category.title'), //分类页面title
        ]);

        //添加操作日志
        LogModel::write(
            $this->userId,
            GoodsLogConstant::GOODS_CATEGORY_SETTING,
            GoodsLogConstant::getText(GoodsLogConstant::GOODS_CATEGORY_SETTING),
            0,
            [
                'log_data' => $post,
                'log_primary' => [
                    '分类层级' => $post['level'] == 1 ? '一级' : ($post['level'] == 2 ? '二级' : '三级'),
//                '样式' => $post['style'] ?: "",
//                '广告' => $post['adv_url'] ?: '',
                ],
            ]
        );

        return $this->success();
    }

    /**
     * 获取设置
     * @author 青岛开店星信息技术有限公司
     */
    public function actionGetSetting()
    {
        $result = ShopSettings::get('goods_category');
        $result['title'] = $result['title'] ?? ShopSettings::getDefaultSettings('goods_category.title'); // 商品分类页面默认名称
        return $this->success(['data' => $result]);
    }

    /**
     * 分类开关
     * @return \yii\web\Response
     * @throws GoodsException
     * @author 青岛开店星信息技术有限公司
     */
    public function actionSwitch(): \yii\web\Response
    {
        $id = RequestHelper::postInt('id');
        if (empty($id)) {
            throw new GoodsException(GoodsException::CATEGORY_SWITCH_PARAMS_ERROR);
        }

        $category = GoodsCategoryModel::findOne($id);
        if (empty($category)) {
            throw new GoodsException(GoodsException::CATEGORY_SWITCH_CATEGORY_NOT_FOUND_ERROR);
        }

        $category->status = $category->status == 1 ? 0 : 1;

        //添加操作日志
        LogModel::write(
            $this->userId,
            GoodsLogConstant::GOODS_CATEGORY_SWITCH,
            GoodsLogConstant::getText(GoodsLogConstant::GOODS_CATEGORY_SWITCH),
            $id,
            [
                'log_data' => $category->attributes,
                'log_primary' => $category->getLogAttributeRemark([
                    'status' => $category->status == 1 ? '启用' : '禁用'
                ]),
            ]
        );

        $category->save();

        return $this->success();
    }

}
